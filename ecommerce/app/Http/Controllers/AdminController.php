<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Category;
use App\Models\Product;


class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderby('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;

        $this->GenerateBrandThumbnailImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand added successfully');
    }
    
    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:brands,slug,' . $request->id,
        'image' => 'nullable|mimes:jpg,jpeg,png|max:2048',
    ]);

    $brand = Brand::find($request->id);
    $brand->name = $request->name;
    $brand->slug = Str::slug($request->slug);

    if ($request->hasFile('image')) {
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) 
        {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }

        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;

        $this->GenerateBrandThumbnailImage($image, $file_name);
        $brand->image = $file_name;
    }

    $brand->save();
    return redirect()->route('admin.brands')->with('status', 'Brand updated successfully');
}


    public function GenerateBrandThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) 
            {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }
            $brand->delete();
            return redirect()->route('admin.brands')->with('status', 'Brand deleted successfully');
        }
        return redirect()->route('admin.brands')->with('error', 'Brand not found');
    }

    public function categories()
    {
        $categories = Category::orderby('id', 'DESC')->paginate(10);
        return view('admin.categories',compact('categories'));
    }
    public function category_add()
    {
        return view('admin.category-add');
    }
    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        $category = new category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateCategoryThumbnailImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'category added successfully');
    }
    public function GenerateCategoryThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }
    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    { 
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }

            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;

            $this->GenerateCategoryThumbnailImage($image, $file_name);
            $category->image = $file_name;
        }

        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category updated successfully');
    }
    public function category_delete($id)
    {
        $category = Category::find($id);
        if ($category) 
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) 
            {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }
            $category->delete();
            return redirect()->route('admin.categories')->with('status', 'Category deleted successfully');
        
    }
    public function products()
    {
        $products = Product::orderby('created_at', 'DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }
    public function product_add()
    {
        $categories = Category::select('id', 'name')->orderBY('name')->get();
        $brands = Brand::select('id', 'name')->orderBY('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }
}