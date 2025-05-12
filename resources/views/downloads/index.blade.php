@extends('basic-app')

@section('content')
    <section class="section-block page-section" id="downloads">
        <div class="section-h2">
            <h2>
                Téléchargements
            </h2>
        </div>
        <div class="section-content">
            <ul>
                @foreach ($documents as $doc)
                    <li class="file-item">
                        <a href="{{ route('download.download', ['documentToken' => $doc->token, 'inline' =>  1]) }}">
                            <i class="far {{ $doc->faCssClass }} icon"></i>
                            <span class="title">{{ $doc->title }}</span>
                            <!--span class="lang">//item.lang ? '(' ~ item.lang|upper ~ ')' </span-->
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="text-block">Pour obtenir les fichiers sons, contacter
                <em>ftiymusic [at] gmail.com</em>
            </p>
        </div>
    </section>
@endsection


