@props(['title', 'image', 'url' => null])

@if($url)
    <a href="{{ $url }}" class="block w-[287px] h-[263px] bg-white rounded-lg border border-gray-300">
@else
    <div class="block w-[287px] h-[263px] bg-white rounded-lg border border-gray-300">
@endif

    <div class="w-full h-full px-[20px] pt-[23px] pb-[24px] flex flex-col gap-6">
        <div class="w-[206px] h-[141px] mx-auto rounded overflow-hidden bg-gray-100">
            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover">
        </div>

        <div class="mt-4 text-[16px] font-medium text-center">
            {{ $title }}
        </div>
    </div>

@if($url)
    </a>
@else
    </div>
@endif

