@extends('layouts.app')

@section('title', ($showcaseContext['heading'] ?? 'Showcase Karya Mahasiswa') . ' - KSM Multimedia')

@section('content')
    @include('partials.showcase-content')
@endsection
