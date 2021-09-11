<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Wazaaapp</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato&family=Roboto+Condensed:wght@300&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Lato', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }
            
            h1, h2, h3, h4, h5, h6 {
                color: #636b6f;
                font-family: 'Roboto Condensed', sans-serif;
                font-weight: 200;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
                width: 100%;
            }

            .title {
                font-size: 84px;
            }
            .subtitle {
                font-size: 30px;
                margin-bottom: 50px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 1px;
            }

            .map {
                width: 100%;
                height: 350px;
                background-color: grey;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <h1 class="title m-b-md">
                    Wazaaapp
                </h1>
                <div class="subtitle">
                    Know what's up
                </div>

                
                <div class="links">
                    
                </div>
                <div id="map" class="map">

                </div>    
            </div>
        </div>
    </body>
    <script src="js/frontPage.js?time={{time()}}"></script>
</html>
