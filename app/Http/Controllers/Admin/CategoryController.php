<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Toastr;
use Image;
use File;
use Str;
class CategoryController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:category-list|category-create|category-edit|category-delete', ['only' => ['index','store']]);
         $this->middleware('permission:category-create', ['only' => ['create','store']]);
         $this->middleware('permission:category-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:category-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Category::orderBy('id','DESC')->get();
        return view('backEnd.category.index',compact('data'));
    }
    public function create()
    {
        return view('backEnd.category.create');
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'status' => 'required',
        ]);
        // image with intervention 
        $image = $request->file('image');
        if($image){
        $name =  time().'-'.$image->getClientOriginalName();
        $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp',$name);
        $name = strtolower(preg_replace('/\s+/', '-', $name));
        $uploadpath = 'public/uploads/category/';
        $imageUrl = $uploadpath.$name; 
        $img=Image::make($image->getRealPath());
        $img->encode('webp', 90);
        $width = "400";
        $height = "400";
        $img->height() > $img->width() ? $width=null : $height=null;
        $img->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save(public_path('uploads/category/' . $name));
        }else{
            $imageUrl = null;
        }
        // image with intervention 
        $image1 = $request->file('banner');
        if($image1){
        $name1 =  time().'-'.$image1->getClientOriginalName();
        $name1 = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp',$name1);
        $name1 = strtolower(preg_replace('/\s+/', '-', $name1));
        $uploadpath1 = 'public/uploads/category/';
        $imageUrl1 = $uploadpath1.$name1; 
        $img1=Image::make($image1->getRealPath());
        $img1->encode('webp', 90);
        $width1 = "";
        $height1 = "";
        $img1->height() > $img1->width() ? $width1=null : $height1=null;
        $img1->resize($width1, $height1, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img1->save(public_path('uploads/category/' . $name1));
        }else{
            $imageUrl1 = null;
        }

        $input = $request->all();
        $input['slug'] = strtolower(preg_replace('/\s+/', '-', $request->name));
        $input['slug'] = str_replace('/', '', $input['slug']);

        $input['front_view'] = $request->front_view ? 1 : 0;
        $input['banner_status'] = $request->banner_status ? 1 : 0;
        $input['highlight'] = $request->highlight ? 1 : 0;
        $input['image'] = $imageUrl;
        $input['banner'] = $imageUrl1;
        Category::create($input);
        Toastr::success('Success','Data insert successfully');
        return redirect()->route('categories.index');
    }
    
    public function edit($id)
    {
        $edit_data = Category::find($id);
        $categories = Category::select('id','name')->get();
        return view('backEnd.category.edit',compact('edit_data','categories'));
    }
    
    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $update_data = Category::find($request->id);
        $input = $request->all();
        
        $image = $request->file('image');
        if($image){
            // image with intervention 
            $name =  time().'-'.$image->getClientOriginalName();
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp',$name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $uploadpath = 'public/uploads/category/';
            $imageUrl = $uploadpath.$name; 
            $img=Image::make($image->getRealPath());
            $img->encode('webp', 90);
            $width = "400";
            $height = "400";
            $img->height() > $img->width() ? $width=null : $height=null;
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            File::delete($update_data->image);
            $img->save(public_path('uploads/category/' . $name));
            $input['image'] = $imageUrl;
        }else{
            $input['image'] = $update_data->image;
        }
        
        $image1 = $request->file('banner');
        if($image1){
            // image with intervention 
            $name1 =  time().'-'.$image1->getClientOriginalName();
            $name1 = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp',$name1);
            $name1 = strtolower(preg_replace('/\s+/', '-', $name1));
            $uploadpath1 = 'public/uploads/category/';
            $imageUrl1 = $uploadpath1.$name1; 
            $img1=Image::make($image1->getRealPath());
            $img1->encode('webp', 90);
            $width1 = "";
            $height1 = "";
            $img1->height() > $img1->width() ? $width1=null : $height1=null;
            $img1->resize($width1, $height1, function ($constraint) {
                $constraint->aspectRatio();
            });
            File::delete($update_data->banner);
            $img1->save(public_path('uploads/category/' . $name1));
            $input['banner'] = $imageUrl1;
        }else{
            $input['banner'] = $update_data->banner;
        }
        
        $input['slug'] = strtolower(preg_replace('/\s+/', '-', $request->name));
        $input['slug'] = str_replace('/', '', $input['slug']);
        $input['front_view'] = $request->front_view ? 1 : 0;
        $input['status'] = $request->status?1:0;
        $input['banner_status'] = $request->banner_status ? 1 : 0;
        $input['highlight'] = $request->highlight?1:0;
        $update_data->update($input);
        Toastr::success('Success','Data update successfully');
        return redirect()->route('categories.index');
    }
 
    public function inactive(Request $request)
    {
        $inactive = Category::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success','Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Category::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success','Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $category = Category::find($request->hidden_id);
        
        foreach ($category->subcategories ?? [] as $subcategory) {
            foreach ($subcategory->childcategories ?? [] as $childCategory) {
                $childCategory->delete();
            }
            File::delete($subcategory->image);
            $subcategory->delete();
        }
        foreach ($category->products ?? [] as $product) {
            foreach ($product->variables ?? [] as $variable) {
                File::delete($variable->image);
                $variable->delete();
            }
            foreach ($product->images ?? [] as $image) {
                File::delete($image->image);
                $image->delete();
            }
            foreach ($product->reviews ?? [] as $review) {
                $review->delete();
            }
            foreach ($product->campaigns ?? [] as $campaign) {
                File::delete($product->banner);
                $campaign->delete();
            }
            File::delete($product->image);
            $product->delete();
        }
        File::delete($category->image);
        $category->delete();
        Toastr::success('Success','Data delete successfully');
        return redirect()->back();
    }
}
