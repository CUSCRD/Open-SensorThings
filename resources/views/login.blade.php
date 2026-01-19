<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Open SensorThings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{asset('css/login.css')}}">
</head>

<body>

    <div class="container">
        <div class="card">

            {{-- Header --}}
            <div class="header">
                <div class="logo">
                    <i class="fa-solid fa-satellite-dish"></i>
                </div>
                <h1>Open SensorThings</h1>
                <div class="version">Can Tho University Software Center</div>

                <p class="subtitle">
                    A modern implementation of the <strong>OGC SensorThings API</strong>,
                    providing standardized access to sensor data and taskable IoT devices.
                </p>
            </div>

            {{-- Standard Overview --}}
            <div class="section">
                <h2>
                    <i class="fa-solid fa-book-open"></i>
                    OGC SensorThings API Implementation
                </h2>

                <p class="subtitle">
                    The Open SensorThings system implements two core parts of the OGC SensorThings API standard,
                    following RESTful design principles and JSON-based data exchange as defined by OGC.
                </p>
            </div>

            {{-- Sensing & Tasking --}}
            <div class="section grid">

                {{-- Sensing --}}
                <div class="box">
                    <h3>
                        <i class="fa-solid fa-wave-square"></i>
                        Sensing
                    </h3>

                    <p>
                        The Sensing component provides standardized access to sensor observations
                        and metadata based on the OGC Observation & Measurement (O&M) model.
                    </p>

                    <ul>
                        <li>Manages Things, Locations, Sensors, Datastreams, and Observations</li>
                        <li>Supports spatial, temporal, and attribute-based queries</li>
                        <li>RESTful API with JSON and OData query support</li>
                    </ul>

                    <div class="tag">Implemented Version: 2.2</div>
                    <div class="tag">Standard: Sensing v1.1</div>
                </div>

                {{-- Tasking --}}
                <div class="box">
                    <h3>
                        <i class="fa-solid fa-sliders"></i>
                        Tasking
                    </h3>

                    <p>
                        The Tasking component enables parameterization and control of task-capable
                        IoT devices, allowing clients to submit tasks dynamically.
                    </p>

                    <ul>
                        <li>Defines tasking capabilities and parameters</li>
                        <li>Submits and manages task execution requests</li>
                        <li>Simplified alternative to traditional OGC SPS services</li>
                    </ul>

                    <div class="tag">Implemented Version: 1.2</div>
                    <div class="tag">Standard: Tasking v1.0</div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="footer">
                © {{ date('Y') }} <span>Open SensorThings</span> ·
                Conformant with OGC SensorThings API
            </div>

        </div>
    </div>

</body>

</html>