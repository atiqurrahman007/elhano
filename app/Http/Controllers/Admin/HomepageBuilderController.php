<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class HomepageBuilderController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order', 'asc')->get();
        return view('backEnd.homepagebuilder.index', compact('sections'));
    }

    public function create()
    {
        $products = Product::where('status', 1)->select('id', 'name', 'new_price')->with('image')->get();
        $categories = Category::where('status', 1)->select('id', 'name')->get();
        return view('backEnd.homepagebuilder.create', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'type' => 'required',
            'status' => 'required',
        ]);

        $params = $request->except(['_token', 'title', 'heading', 'type', 'status', 'sort_order', 'files']);
        
        // Handle Image Uploads
        if ($request->hasFile('image')) {
             if(is_array($request->file('image'))) {
                $images = [];
                foreach($request->file('image') as $key => $image) {
                    $name = time() . $key . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/homepage/'), $name);
                    $images[] = 'public/uploads/homepage/' . $name;
                }
                $params['images'] = $images;
             } else {
                $image = $request->file('image');
                $name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/homepage/'), $name);
                $params['image'] = 'public/uploads/homepage/' . $name;
             }
        }

        HomepageSection::create([
            'title' => $request->title,
            'heading' => $request->heading,
            'type' => $request->type, // This maps to section_key
            'section_key' => $request->type,
            'sort_order' => $request->sort_order ?? 99,
            'status' => $request->status,
            'params' => $params,
        ]);

        Toastr::success('Success', 'Section created successfully');
        return redirect()->route('homepagebuilder.index');
    }

    public function edit($id)
    {
        $section = HomepageSection::find($id);
        // Ensure section_key is always a scalar string
        if (is_array($section->section_key)) {
            $section->section_key = implode('_', $section->section_key);
        }
        $section->section_key = (string) ($section->section_key ?? '');

        $products = Product::where('status', 1)->select('id', 'name', 'new_price')->with('image')->get();
        $categories = Category::where('status', 1)->select('id', 'name')->get();
        return view('backEnd.homepagebuilder.edit', compact('section', 'products', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $section = HomepageSection::find($id);
        $params = $section->params ?? [];

        // Exclude structural & remove-flag fields from params
        $newParams = $request->except([
            '_token', '_method', 'title', 'heading', 'type', 'status',
            'sort_order', 'files', 'remove_image', 'remove_images',
        ]);

        // Handle single image removal
        if ($request->input('remove_image') == '1') {
            unset($params['image']);
            unset($newParams['image']);
        }

        // Handle slider image removal by index
        if ($request->has('remove_images') && is_array($request->input('remove_images'))) {
            $existingImages = $params['images'] ?? [];
            foreach ($request->input('remove_images') as $removeIndex) {
                unset($existingImages[(int)$removeIndex]);
            }
            $params['images'] = array_values($existingImages);
        }

        // Handle Image Uploads
        if ($request->hasFile('image')) {
            if (is_array($request->file('image'))) {
                $images = $params['images'] ?? [];
                foreach ($request->file('image') as $key => $image) {
                    $name = time() . $key . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/homepage/'), $name);
                    $images[] = 'public/uploads/homepage/' . $name;
                }
                $newParams['images'] = $images;
            } else {
                $image = $request->file('image');
                $name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/homepage/'), $name);
                $newParams['image'] = 'public/uploads/homepage/' . $name;
            }
        } else {
            // Keep existing images if no new ones uploaded
            if (isset($params['images'])) $newParams['images'] = $params['images'];
            if (isset($params['image']) && $request->input('remove_image') != '1') $newParams['image'] = $params['image'];
        }

        $section->update([
            'title'      => $request->title,
            'heading'    => $request->heading,
            'status'     => (int) $request->input('status', 0),
            'sort_order' => $request->sort_order,
            'params' => array_merge($params, $newParams)
        ]);

        Toastr::success('Success', 'Section updated successfully');
        return redirect()->route('homepagebuilder.index');
    }

    public function destroy($id)
    {
        $section = HomepageSection::find($id);
        $section->delete();
        Toastr::success('Success', 'Section deleted successfully');
        return back();
    }

    public function reorder(Request $request)
    {
        $sections = HomepageSection::all();
        foreach ($sections as $section) {
            $section->timestamps = false; // To avoid updating updated_at column
            $id = $section->id;
            foreach ($request->order as $order) {
                if ($order['id'] == $id) {
                    $section->update(['sort_order' => $order['position']]);
                }
            }
        }
        return response()->json(['status' => 'success']);
    }
}
