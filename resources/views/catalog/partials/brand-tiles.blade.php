@php
    use App\Models\Category;
    use App\Models\Product;
    use App\Models\Brand;

    // Все подкатегории этой категории
    $subcategoryIds = Category::where('parent_id', $category->id)->pluck('id');

    // Первая подкатегория (например, "Смартфоны")
    $subcategory = Category::where('parent_id', $category->id)->first();

    // Название подкатегории (для плиток)
    $subcategoryTitle = $subcategory?->title ?? '';

    // Бренды товаров из всех подкатегорий
    $brandIds = Product::whereIn('category_id', $subcategoryIds)
        ->pluck('brand_id')
        ->unique();

    $brands = Brand::whereIn('id', $brandIds)->get();
@endphp

@foreach($brands as $brand)
    @php
        // Название плитки: "Смартфоны Apple"
        $brandTitle = $subcategoryTitle . ' ' . mb_convert_case($brand->title, MB_CASE_TITLE);

        // Путь к картинке
        $imagePath = "/storage/images/category-images/{$group->slug}/{$category->slug}/{$subcategory->slug}-{$brand->slug}.jpg";

        // Ссылка на подкатегорию с фильтром бренда
        $url = route('catalog.subcategory.brand', [
            'group'       => $group->slug,
            'category'    => $category->slug,
            'subcategory' => $subcategory->slug,
            'brands'      => $brand->slug,
        ]);
    @endphp

    <x-catalog-card
        :title="$brandTitle"
        :image="$imagePath"
        :url="$url"
    />
@endforeach
