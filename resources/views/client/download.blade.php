@extends('layouts.app')

@section('title', 'Download')

@section('content')
<div class="container">
    <h1 class="text-center mb-0">Download {{ config('app.name') }}</h1>
    <p class="text-center">Download the {{ config('app.name') }} client in order play games with your friends, host servers of your own, or to create classic Roblox games.</p>
    <hr>
    <div id="nextSlide" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="d-block w-100" src="{{ asset('images/site/download/img1.png') }}" alt="{{ config('app.name') }} Screenshot">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/site/download/img2.png') }}" alt="{{ config('app.name') }} Screenshot">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/site/download/img3.png') }}" alt="{{ config('app.name') }} Screenshot">
            </div>
        </div>
        <a class="carousel-control-prev" href="#nextSlide" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#nextSlide" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
    <div class="text-center">
        <a class="btn btn-lg btn-success dl-client mt-4" style="width: 35%" role="button" href="https://cdn.discordapp.com/attachments/1118772952830328832/1147586539585548338/Crapish-1.0.2.exe">Download Client</a>
        <a role="button" href="" class="btn btn-lg btn-success dl-mobile mt-4 disabled" style="width: 35%; margin-left: 0.75%">Download Mobile</a>
    </div>
</div>
<div class="modal fade" id="DownloadedClient" tabindex="-1" aria-labelledby="DownloadedClientLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="DownloadedClientLabel">{{ config('app.name') }} Client</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col">
                            <h2 class="font-weight-normal">1.</h2>
                            <p>Save the file to your computer</p>
                        </div>
                        <div class="col">
                        <h2 class="font-weight-normal">2.</h2>
                            <p>Run {{ config('app.name') }} installer</p>
                        </div>
                        <div class="col">
                        <h2 class="font-weight-normal">3.</h2>
                            <p>Start joining and hosting one of many {{ config('app.name') }} games!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="DownloadClient" tabindex="-1" aria-labelledby="DownloadedClientLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="DownloadedClientLabel">{{ config('app.name') }} Mobile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col">
                            <h2 class="font-weight-normal">1.</h2>
                            <p>Choose your phone type below</p>
                        </div>
                        <div class="col">
                        <h2 class="font-weight-normal">2.</h2>
                            <p>Install and Sign in</p>
                        </div>
                        <div class="col">
                        <h2 class="font-weight-normal">3.</h2>
                            <p>Start joining one of many {{ config('app.name') }} games!</p>
                        </div>
                        <hr>
                     <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <b>Mobile is still very early beta.</b> 
                     </div>
                     <div class="mx-auto text-center">
                        <a class="btn btn-lg btn-success dl-client mt-4 mr-1" style="width: 35%" role="button" href="#">Download Android</a>
                        <a class="btn btn-lg btn-success dl-client mt-4 disabled" style="width: 35%" role="button" href="#">Download iOS</a>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    $(".dl-client").on("click", function() {
        $("#DownloadedClient").modal("show");
    })
    $(".dl-mobile").on("click", function() {
        $("#DownloadedMobile").modal("show");
    })

</script>
@endsection
