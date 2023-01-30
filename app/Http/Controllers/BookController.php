<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = Book::query()->paginate(10);

        // dd($data);
        return Inertia::render('books', [
            'data' => $data
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreBookRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreBookRequest $request)
    {
        Validator::make($request->all(), [
            'title' => 'required',
            'author' => 'required'
        ])->validate();
        $book = Book::create($request->only(['title', 'author']));

        $this->processImage($request, $book);

        return redirect()->back()
            ->with('message', 'Book created');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateBookRequest $request
     * @param \App\Models\Book $book
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        Validator::make($request->all(), [
            'title' => 'required',
            'author' => 'required'
        ])->validate();
        $book->update($request->only('title', 'author'));

        $this->processImage($request, $book);

        return redirect()->back()
            ->with('message', 'Book Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Book $book
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->back()
            ->with('message', 'Book deleted');
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('imageFilepond')) {
            return $request->file('imageFilepond')->store('uploads/books', 'public');
        }
        return '';
    }
    public function uploadRevert(Request $request)
    {
        if ($image = $request->get('image')) {
            $path = storage_path('app/public/' . $image);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function processImage(Request $request, Book $book = null)
    {
        $images = $request->get('image') ? explode('|', $request->get('image')) : [];

        foreach($images as $image)
        {
            if(!$book->hasImage($image)){
                $path = storage_path('app/public/' . $image);
                if(file_exists($path)){
                    copy($path, public_path($image));
                    unlink($path);
                }
            }
        }

        foreach($book->findMissingImages($images) as $img)
        {
            if(file_exists(public_path($img))){
                unlink(public_path($img));
            }
        }

        $book->update([
            'image' => $request->get('image')
        ]);
//        if ($image = $request->get('image')) {
//            $path = storage_path('app/public/' . $image);
//            if (file_exists($path)) {
//                copy($path, public_path($image));
//                unlink($path);
//            }
//        }
//        if ($book)
//        {
//            if (!$request->get('image'))
//            {
//                if ($book->image)
//                {
//                    if (file_exists(public_path($book->image))){
//                        unlink(public_path($book->image));
//                    }
//                }
//            }
//            $book->update([
//                'image' => $request->get('image')
//            ]);
//        }
    }
}
