<div class="grid grid-cols-4 gap-6">

    @forelse($products as $product)
        <div class="bg-white p-4 rounded shadow-sm">

            <a href="#" class="block">
                <img src="{{ $product->image_url }}"
                     alt="{{ $product->title }}"
                     class="w-full h-[200px] object-cover rounded">
            </a>

            <div class="mt-3 text-[15px] font-medium">
                {{ $product->title }}
            </div>

            <div class="mt-2 text-[17px] font-semibold text-red-600">
                {{ number_format($product->price, 0, ',', ' ') }} <i class="nbrb-icon">BYN</i>
            </div>

        </div>
    @empty
        <p class="text-gray-500">Товаров пока нет.</p>
    @endforelse

</div>

@if(method_exists($products, 'links'))
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@endif
