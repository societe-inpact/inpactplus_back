@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex mb-4 gap-2">
        <button class="btn btn-success">Créer un nouvel utilisateur</button>
        <button class="btn btn-primary">Autre action</button>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">{{ __('Liste des utilisateurs') }}</div>

                <div class="card-body">
                    <ul>
                        @foreach($users as $user)
                            <li>{{$user->email}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">{{ __('Dossiers clients') }}</div>

                <div class="card-body">
                    @foreach($folders as $folder)
                        <li>{{$folder->folder_number}}</li>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
