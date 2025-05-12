<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @vite([
        'resources/js/app.js',
        'resources/css/app.css',
        'resources/css/fontawesome-pro-5.12.0-web/css/all.css',
    ])

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>
</head>
<body class="font-sans antialiased">
<div id="global">

    <!--div id="header">
        <h1>Fairy Tales in Yoghourt</h1>
    </div-->
    <div id="content">

        @yield('content')
    </div>

    <!--section id="footer">
        <p>
                        <span>Contact : ftiymusic [at] gmail.com<span>
                        - <a href="https://www.instagram.com/fairytalesinyoghourt/">Instagram</a>
                        - <a href="http://fairytalesinyoghourt.bandcamp.com">Bandcamp</a>
                        - <a href="https://soundcloud.com/walkertakethatrangers">Soundcloud</a>
                        - <a href="https://www.facebook.com/FairyTalesInYoghourt/">Facebook</a>
                        - <a rel="me" href="https://mastodon.social/@theredled">Mastodon</a>
        </p>
        <p>
            <a href="https://www.facebook.com/ClasseMannequin">Classe Mannequin</a>
            - <a href="https://www.facebook.com/BantamLyons/">Bantam Lyons</a>
            - <a href="https://www.facebook.com/BlondNeilYoung/">Blond Neil Young</a>
            - <a href="https://www.facebook.com/alvonstramm/">Al Von Stramm</a>
        </p>
    </section-->
</div>

</body>
</html>
