@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Éditer {{ ${{$modelVar}}->name }}</h1>
    <form action="{{ route('{{$table}}.update', ${{$modelVar}}) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf @method('PUT')
        @foreach ($fields as $field)
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="{{ $field['name'] }}">
                {{ ucfirst($field['name']) }}
            </label>
            @if ($field['type'] === 'text')
                <textarea name="{{ $field['name'] }}" id="{{ $field['name'] }}" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old($field['name'], ${{$modelVar}}->{$field['name']}) }}</textarea>
            @elseif ($field['type'] === 'boolean')
                <input type="checkbox" name="{{ $field['name'] }}" value="1" class="mr-2 leading-tight" {{ old($field['name'], ${{$modelVar}}->{$field['name']}) ? 'checked' : '' }}>
            @else
                <input type="{{ $field['type'] === 'float' ? 'number' : 'text' }}" name="{{ $field['name'] }}" id="{{ $field['name'] }}" value="{{ old($field['name'], ${{$modelVar}}->{$field['name']}) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" {{ $field['type'] === 'float' ? 'step="any"' : '' }}>
            @endif
            @error($field['name'])
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>
        @endforeach
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Mettre à jour
            </button>
        </div>
    </form>
</div>
@endsection