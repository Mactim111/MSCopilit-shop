@extends('layouts.main')
@section('content')
    <section class="w-full">
        <div class="max-w-[1500px] mx-auto">
            
            {{-- Просто перебираем все секции, которые админ настроил в БД --}}
            @foreach($sections as $section)
                <x-section-renderer :section="$section" />
            @endforeach

        </div>
    </section>
@endsection
