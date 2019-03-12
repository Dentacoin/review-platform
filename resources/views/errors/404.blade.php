<!DOCTYPE html>
<html>
    <head>
        <title>Not found - 404</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato', sans-serif;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
                padding: 0px 100px;
            }

            .button {
                font-size: 37px;
                border: 1px solid #B0BEC5;
                display: inline-block;
                padding: 10px 40px;
                color: #b0bec5;
                text-decoration: none;
                border-radius: 10px;
                background-color: transparent;
                transition: all 0.3s ease;
            }

            .button:hover {
                background-color: #b0bec5;
                color: white;
                transition: all 0.3s ease;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Sorry, the page you are looking for can't be found. Click the button to go to the home page.</div>
                <a class="button" href="{{ getLangUrl('/') }}">Home</a>
            </div>
        </div>
    </body>
</html>
