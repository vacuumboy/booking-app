<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Расписание мастеров</h1>
            <div>
                <a href="{{ route('salon.masters.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                    Управление мастерами
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="text-center py-8">
                <div class="text-5xl mb-4">👨‍💼</div>
                <h2 class="text-xl font-semibold mb-4">У вашего салона пока нет мастеров</h2>
                <p class="text-gray-600 mb-6">Для управления расписанием сначала добавьте мастеров в ваш салон.</p>
                <a href="{{ route('salon.masters.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg">
                    + Добавить мастера
                </a>
            </div>
        </div>
    </div>
</x-app-layout> 