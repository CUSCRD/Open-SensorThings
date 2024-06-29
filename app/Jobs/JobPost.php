<?php

namespace App\Jobs;

use App\Http\Controllers\OGC\PostController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\HeaderBag;

class JobPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    private $header;
    private $param;
    private $result;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request,HeaderBag $headerBag,$param=null)
    {
        $this->request=$request;
        $this->header=$headerBag;
        $this->param=$param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->result=PostController::post($this->request,$this->header,$this->param);
    }
    public function getResult(){
        return $this->result;
    }
}
