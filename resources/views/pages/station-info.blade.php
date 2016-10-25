@extends('main')

@section('title', 'Station-info')

@section('activePageStation', 'active')

@section('content')
  <div class="row">
    <div class="linkerdeel col-lg-3">
      <div class="col-lg-10 col-lg-offset-1">
        {!! Form::open(array('route' => 'station-info.zoek')) !!}
            {{Form::label('name', 'Station:')}}
            {{Form::input('text', 'name', null, array('class' => 'form-control'))}}
            <br>
            {{Form::label('treinTijd', 'Tijdstip:')}}
            {{Form::input('datetime-local', 'treinTijd', null, array('class' => 'form-control'))}}

             {{Form::submit('Zoek', array('class' => 'btn btn-success zoekBtn btn-block'))}}
        {!! Form::close() !!}
      </div>
    </div>

    <div class="rechterdeel col-lg-9">
      {!!$data or ""!!}

    </div>
  </div>
@endsection

@section('scripts')
  <script src="/js/website.min.js"></script>
@endsection