@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Liste des Tests</h1>
    <a href="{{ route('tests.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">Créer</a>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">Nom</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tests as $test)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $test->id }}</td>
                    <td class="py-2 px-4 border-b">{{ $test->name }}</td>
                    <td class="py-2 px-4 border-b">
                        <a href="{{ route('tests.edit', $test) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Éditer</a>
                        <form action="{{ route('tests.destroy', $test) }}" method="POST" class="inline-block">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection