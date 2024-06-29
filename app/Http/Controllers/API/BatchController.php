<?php


namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BatchController
{
    //các key là ngẫu nhiên cho mỗi lần nhận gói tin từ client, key gửi cho client cũng là ngẫu nhiên
    //đây chỉ là mẫu thử nghiệm, chưa được phát triển hoàn chỉnh
    //mặc dù vậy, nó vẫn hoạt động chính xác
    public const BATCH_REQUEST_KEY = "batch_36522ad7-fc75-4b56-8c71-56071383e77b";
    public const CHANGE_SET_REQUEST_KEY = 'changeset_77162fcd-b8da-41ac-a9f8-9357efbbd';
    public const BATCH_RESPONSE_KEY = 'b_243234_25424_ef_892u748';
    public const CHANGE_SET_RESPONSE_KEY = 'cs_12u7hdkin252452345eknd_383673037';


    public const JSON_CONTENT_TYPE = 'application/json';
    public const HTTP_CONTENT_TYPE = 'application/http';
    public const CHANGE_SET_CONTENT_TYPE = 'multipart/mixed;boundary=' . self::CHANGE_SET_REQUEST_KEY;



    public function action(Request $request): JsonResponse
    {
        if (static::validateBoundaryHeader($request, static::BATCH_REQUEST_KEY)) {
            $user = $request->header('php-auth-user');
            $password = $request->header('php-auth-pw');
            try {
                $content = static::handleRequestString($request->getContent(), $user, $password);
            } catch (Exception $exception) {
                if ($exception->getCode() == 0) {
                    $code = 400;
                    $message = 'invalid data';
                    // sử dụng $message=$exception->getMessage(); để debug trong trường hợp dữ liệu không lỗi
                } else {
                    $message = $exception->getMessage();
                    $code = $exception->getCode();
                }
                return response()->json(['error' => $message], $code);
            }
            return response()->json($content);
        } else {
            return response()->json(['error' => 'invalid Content-Type'], 412);
        }
    }

    //kiểm tra header có hợp lệ không
    public static function validateBoundaryHeader(Request $request, string $key): bool
    {
        $pattern = '/boundary=(.*)/';
        preg_match($pattern, $request->header('Content-Type'), $match);
        if ($match) {
            $matchKey = $match[1];
            if ($matchKey == $key) {
                return true;
            }
        }
        return false;
    }


    /**
     * @throws Exception
     */
    public static function handleGetRequest(string $requestURL, string $user, string $password)
    {
        $request = Request::create($requestURL);
        $request->headers->set('php-auth-user', $user);
        $request->headers->set('php-auth-pw', $password);
        return app()->handle($request);
    }

    /**
     * @throws Exception
     */
    public static function handleChangeSetRequest(array $insertRequestArray, string $uri, string $method, string $user, $password, array $customHeader = [])
    {
        //sample header:
        //        {"location":["get\/sensors(30)"],"cache-control":["no-cache, private"],"date":["Fri, 27 Aug 2021 11:10:43 GMT"],"content-type":["application\/json"],"set-cookie":["XSRF-TOKEN=eyJpdiI6InJrWGtKd2pSQy9SUk1XRFJTZG84WHc9PSIsInZhbHVlIjoib3N2b3U3dy8rdnI3cXkrcFhPQkFUbXc3M09IM3paeGhLWGQ2c0lEd2tudEJMclF1Z0JpOEFtODlVQ3NPcWV0cmNzbHRZZHNkUFc4S1dJWEF5UjFGdC95c050K2tDRmxuN21OYlllWDljcTQ4TXJiS1FHcFNPQWpzSkh5TUVmS2giLCJtYWMiOiJiNDZmNDI3YzgzZjQ2ZGY1YmQ3MzkwZGYwNjdjNjBmNTAyOGU5YjRjMmM1OWI5ZmM1MTAyZTVmMDM3OWU4NDA0In0%3D; expires=Fri, 27-Aug-2021 13:10:43 GMT; Max-Age=7200; path=\/; samesite=lax","laravel_session=eyJpdiI6ImQ5YWlyUjBHRUlFL01VZnhOSkZRUXc9PSIsInZhbHVlIjoiOGF6VUkrM09GTXBnY3l6Z1R5NnZ3UEZpcWpjWWQvd09YMDJRbmVmcWFzQ1l3K3U5ZTVtZCtzM3dHeVV0aDZ1S1VxK1lEcUZCSFdWTEFYTU5qT3pXd1pkSkZlVmVselhFTGwyS1BIaWxDclNLbmh6R3VrOHBXRWtPcTI2aENINkEiLCJtYWMiOiJhNjY1N2E5YTYwODRiYjYwMTVhZmJlZTcxYzUyNWVkNTE0MDg3YjM3MGQwM2QzYWJlYWI1ZGFiZjA5MjE5YTg3In0%3D; expires=Fri, 27-Aug-2021 13:10:43 GMT; Max-Age=7200; path=\/; httponly; samesite=lax"]}
        $request = Request::create($uri, $method, [], [], [], [], json_encode($insertRequestArray));
        $request->headers->set('php-auth-user', $user);
        $request->headers->set('php-auth-pw', $password);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');

        //thêm location
        foreach ($customHeader as $key => $value) {
            if (strtoupper($method) == 'POST') {
                $request->headers->set($key, $value);
            }
        }
        return app()->handle($request);
    }

    /**
     * @throws Exception
     */
    public static function handleRequestString(string $object, string $user, string $password): array
    {
        $object = json_decode($object, 1);
        $batchRequests = $object['requests'];
        $result = Collection::empty();
        $header = [
            'Content-Type' => static::JSON_CONTENT_TYPE,
        ];
        foreach ($batchRequests as $itemBatchRequest) {
            if ($itemBatchRequest['boundary'] == static::BATCH_REQUEST_KEY) {
                $contentType = $itemBatchRequest['headers']['Content-Type'];
                if ($contentType == static::HTTP_CONTENT_TYPE) {
                    //get
                    if (strtoupper($itemBatchRequest['method']) == 'GET') {
                        try {
                            try {
                                $getResult = static::handleGetRequest($itemBatchRequest['requestURL'], $user, $password);
                            } catch (Exception $exception) {
                                throw new Exception('cannot do GET request', 500);
                            }
                            $tempArr = [
                                'boundary' => static::BATCH_RESPONSE_KEY,
                                'headers' => ['Content-Type' => static::HTTP_CONTENT_TYPE],
                                'responses' => [
                                    [
                                        'Status' => 200,
                                        "Headers" => [
                                            'Content-Type' => static::JSON_CONTENT_TYPE,
                                        ],
                                        'body' => json_decode($getResult->getContent(), 1)
                                    ]
                                ]
                            ];
                            $result->push($tempArr);
                        } catch (Exception $e) {
                            //lỗi
                        }
                    } else {
                        //lỗi
                        throw new Exception('GET method is required', 412);
                    }
                } else if ($contentType == static::CHANGE_SET_CONTENT_TYPE) {
                    $change_set_requests = $itemBatchRequest['requests'];
                    $idArray = [];
                    $resultSet = Collection::empty();
                    foreach ($change_set_requests as $itemChangeSet) {
                        if ($itemChangeSet['boundary'] == static::CHANGE_SET_REQUEST_KEY) {
                            $headerItemChangeSet = $itemChangeSet['Headers'];
                            if ($headerItemChangeSet['Content-Type'] == static::HTTP_CONTENT_TYPE) {
                                $contentId = $headerItemChangeSet['Content-ID'];
                                $csRequest = $itemChangeSet['requests'];
                                if ($csRequest['Headers']['Content-Type'] == static::JSON_CONTENT_TYPE) {
                                    $body = $csRequest['body'] ?? [];
                                    try {
                                        $changeSetResult = static::handleChangeSetRequest($body, $csRequest['requestURL'], $csRequest['Method'], $user, $password, $idArray);
                                    } catch (Exception $exception) {
                                        throw new Exception('cannot do Change Entity request', 500);
                                    }

                                    if (strtoupper($csRequest['Method']) == 'PATCH') {
                                        $body = json_decode($changeSetResult->getContent(), 1);
                                        //không có nội dung
                                    } else {
                                        if (strtoupper($csRequest['Method']) == 'POST') {
                                            $body = json_decode($changeSetResult->getContent(), 1);
                                            //location tự động đổi sang chữ thường, không phải Location
                                            $location = $changeSetResult->headers->get('location');
                                        } else {
                                            if (strtoupper($csRequest['Method']) == 'DELETE') {
                                                $body = json_decode($changeSetResult->getContent(), 1);
                                            } else {
                                                throw new Exception('invalid method', 405);
                                            }
                                        }
                                    }
                                    $status = json_decode($changeSetResult->getStatusCode());
                                    $response = [
                                        'status' => $status,
                                    ];

                                    //là POST
                                    if (isset($body) && $body != null && (!isset($body['error']) || $body['error'] == null)) {
                                        $response['body'] = $body;
                                        if (isset($location) && $location != null) {
                                            $header['location'] = $location;
                                            //không có lỗi mới thêm header location
                                            $idArray[$contentId] = static::getIdFromLocation($location);
                                            $location = null;
                                            unset($location);
                                        }
                                        $response['Headers'] = $header;
                                    } else {
                                        if (isset($body['error']) != null) {
                                            $response['body'] = $body;
                                        }
                                    }


                                    $tempChangeItem = [
                                        'boundary' => static::CHANGE_SET_RESPONSE_KEY,
                                        'Content-ID' => $contentId,
                                        'Headers' => [
                                            'Content-Type' => static::HTTP_CONTENT_TYPE
                                        ],
                                        'responses' => $response
                                    ];
                                    $resultSet->push($tempChangeItem);
                                }
                            }
                        }
                    }
                    $tempSet = [
                        'boundary' => static::CHANGE_SET_RESPONSE_KEY,
                        'headers' => array_merge([
                            'Content-Type' => 'multipart/mixed;boundary=' . static::CHANGE_SET_RESPONSE_KEY
                        ], $idArray),
                        'responses' => $resultSet->toArray()
                    ];
                    $result->push($tempSet);
                }
            }
        }
        return $result->toArray();
    }

    /**
     * @throws Exception
     */
    private static function getIdFromLocation(STRING $location): int
    {
        $regexPattern = '/(\((\d+)\)\/?)$/';
        preg_match($regexPattern, $location, $match);
        if ($match) {
            return $match[2];
        }
        throw new Exception('internal server error;invalid location string: ' . $location, 500);
    }
}
