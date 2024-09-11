@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pt-3 text-success">
                    <h3>File Processor & OCR Tool</h3>
                </div>

                <div class="card-body">
                    <p class="lead">Seamlessly upload and convert your images to text</p>
                    <upload-component></upload-component>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
