@vite('resources/css/app.css')

<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Liste des Categorys</h1>
    <a href="{{ route('categories.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">Créer</a>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b">ID</th>
                    @foreach ($fields as $field)
                    <th class="py-2 px-4 border-b">{{ ucfirst($field['name']) }}</th>
                    @endforeach
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $category->id }}</td>
                    @foreach ($fields as $field)
                    <td class="py-2 px-4 border-b">
                        {{ $category->{$field['name']} }}
                    </td>
                    @endforeach
                    <td class="py-2 px-4 border-b">
                        <a href="{{ route('categories.edit', $category) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Éditer</a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline-block">
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