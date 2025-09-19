@props(['master'])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
  <div class="p-5">
    <div class="flex items-center">
      <div class="w-20 h-20 rounded-full mr-5 bg-blue-100 flex items-center justify-center overflow-hidden">
        @if($master->photo_path)
          <img src="{{ asset('storage/' . $master->photo_path) }}" alt="{{ $master->name }}" class="w-full h-full object-cover">
        @else
          <span class="text-blue-500 text-2xl font-bold">{{ strtoupper(substr($master->name, 0, 2)) }}</span>
        @endif
      </div>
      <div>
        <h3 class="text-xl font-semibold text-gray-800">{{ $master->name }}</h3>
        <div class="flex items-center space-x-2 mt-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
          <span class="text-gray-600">{{ $master->phone }}</span>
        </div>
        <div class="flex items-center space-x-2 mt-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <span class="text-gray-600">{{ $master->email }}</span>
        </div>
      </div>
    </div>
    
    @if($master->bio)
      <div class="mt-4 p-4 bg-gray-50 rounded-md">
        <h4 class="font-medium text-gray-700 mb-2">О мастере:</h4>
        <p class="text-gray-600 text-sm">{{ Str::limit($master->bio, 150) }}</p>
      </div>
    @endif
    
    <div class="mt-5 flex justify-between items-center">
      <div class="flex space-x-2">
        <a href="{{ route('salon.schedules.index', ['master_id' => $master->id]) }}" 
           class="px-4 py-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
          Расписание
        </a>
        <a href="{{ route('calendar.day', ['date' => now()->format('Y-m-d'), 'master_id' => $master->id]) }}" 
           class="px-4 py-2 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors">
          Записи
        </a>
      </div>
      
      <form action="{{ route('salon.masters.remove', ['master' => $master->id]) }}" 
            method="POST" 
            onsubmit="return confirm('Вы уверены, что хотите удалить этого мастера из салона?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </form>
    </div>
  </div>
</div> 