@extends('email')

@section('message')
<p>Hey {{ $username }},</p>

<p>Thanks for joining! I just need you to do one last, quick thing to finish registration.</p>

<p>Please confirm your email addess by clicking on this link: <a href="{{ app_url('auth/confirm', [$code]) }}">{{ app_url('auth/confirm', [$code]) }}</a></p>

<p></p>

<p>Cheers,</p>

<p>Nik</p>

<p>CEO, PayPerWin
@endsection
