# Koperasi Digital - CSS Component Reference

## Quick Reference Guide

### 🎨 Colors
```
Primary Green: from-koperasi-500 to-koperasi-600
Light Green: koperasi-50, koperasi-100
Dark Green: koperasi-700, koperasi-800, koperasi-900

Blue: blue-50, blue-100, blue-500, blue-600, blue-700
Purple: purple-50, purple-100, purple-500
Red: red-50, red-100, red-500, red-600
Green: green-50, green-100, green-500
Yellow: yellow-100, yellow-800
Gray: gray-50, gray-100, gray-200, gray-500, gray-600, gray-700
```

## Button Classes

### Standard Buttons
```blade
<!-- Primary (Green) -->
<button class="px-6 py-2 bg-gradient-to-r from-koperasi-600 to-koperasi-500 text-white rounded-lg hover:from-koperasi-700 hover:to-koperasi-600 font-medium transition-all shadow-md hover:shadow-lg">
  Primary Button
</button>

<!-- Secondary (White) -->
<button class="px-6 py-2 bg-white border-2 border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all">
  Secondary Button
</button>

<!-- Success (Emerald) -->
<button class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 font-medium transition-all shadow-md">
  Success Button
</button>

<!-- Danger (Red) -->
<button class="px-6 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 font-medium transition-all shadow-md">
  Danger Button
</button>

<!-- Warning (Amber) -->
<button class="px-6 py-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-lg hover:from-amber-600 hover:to-amber-700 font-medium transition-all shadow-md">
  Warning Button
</button>

<!-- Small Button -->
<button class="px-3 py-1 text-xs font-semibold rounded-lg transition-colors">
  Small Button
</button>
```

## Card Classes

### Container Cards
```blade
<!-- Standard Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="px-6 py-4">
    <!-- Content -->
  </div>
</div>

<!-- Card with Header -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="px-6 py-4 bg-gradient-to-r from-koperasi-50 to-koperasi-100 border-b border-gray-200">
    <h3 class="text-lg font-bold text-koperasi-900">Card Title</h3>
  </div>
  <div class="p-6">
    <!-- Content -->
  </div>
</div>

<!-- Stat Card -->
<div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-100">
  <div class="bg-gradient-to-br from-koperasi-50 to-koperasi-100 px-6 py-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-koperasi-700">Label</p>
        <p class="text-3xl font-bold text-koperasi-900 mt-1">Value</p>
      </div>
      <div class="text-4xl opacity-50">🏦</div>
    </div>
  </div>
  <div class="px-6 py-2 border-t border-koperasi-200">
    <p class="text-xs text-koperasi-600">Description</p>
  </div>
</div>
```

## Form Elements

### Input Fields
```blade
<!-- Standard Input -->
<input type="text" 
       placeholder="Enter text..." 
       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-koperasi-500 focus:ring-2 focus:ring-koperasi-100 transition-all" />

<!-- Error Input -->
<input type="text" 
       class="w-full px-4 py-3 border-2 border-red-400 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100" />

<!-- Label -->
<label class="block text-sm font-semibold text-gray-700 mb-2">
  Field Label
</label>

<!-- Select Dropdown -->
<select class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-koperasi-500 focus:ring-2 focus:ring-koperasi-100 transition-all appearance-none cursor-pointer">
  <option>Option 1</option>
  <option>Option 2</option>
</select>

<!-- Checkbox -->
<div class="flex items-center">
  <input id="check" type="checkbox" class="w-4 h-4 text-koperasi-600 rounded focus:ring-koperasi-500 cursor-pointer">
  <label for="check" class="ml-2 text-sm text-gray-700 cursor-pointer">
    Checkbox Label
  </label>
</div>

<!-- Form Group -->
<div class="mb-4">
  <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
  <input type="email" placeholder="email@example.com" 
         class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-koperasi-500 focus:ring-2 focus:ring-koperasi-100" />
  @error('email')
    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
  @enderror
</div>
```

## Badge/Status Classes

### Status Badges
```blade
<!-- Success Badge -->
<span class="px-3 py-1 text-xs font-semibold rounded-full bg-koperasi-100 text-koperasi-800">
  Active
</span>

<!-- Warning Badge -->
<span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
  Pending
</span>

<!-- Danger Badge -->
<span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
  Overdue
</span>

<!-- Info Badge -->
<span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
  Info
</span>

<!-- Gray Badge -->
<span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
  Inactive
</span>
```

## Table Styling

### Table Structure
```blade
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <!-- Header -->
  <div class="px-6 py-4 bg-gradient-to-r from-koperasi-50 to-koperasi-100 border-b border-gray-200">
    <h3 class="text-lg font-bold text-koperasi-900">Table Title</h3>
  </div>

  <!-- Table Container (for responsive) -->
  <div class="overflow-x-auto">
    <table class="w-full divide-y divide-gray-200">
      <!-- Table Head -->
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
            Column 1
          </th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
            Column 2
          </th>
          <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
            Actions
          </th>
        </tr>
      </thead>

      <!-- Table Body -->
      <tbody class="bg-white divide-y divide-gray-200">
        @forelse($items as $item)
        <tr class="hover:bg-koperasi-50 transition-colors duration-150">
          <td class="px-6 py-4 whitespace-nowrap">
            <p class="font-semibold text-gray-900">{{ $item->name }}</p>
            <p class="text-xs text-gray-500">{{ $item->id }}</p>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
            {{ $item->value }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="flex gap-2 justify-center">
              <a href="#" class="px-3 py-1 text-xs font-semibold text-koperasi-600 hover:bg-koperasi-100 rounded transition-colors">
                View
              </a>
              <a href="#" class="px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100 rounded transition-colors">
                Edit
              </a>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="3" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center">
              <span class="text-4xl mb-2">📭</span>
              <p class="text-gray-500 text-lg font-medium">No data found</p>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
    <p class="text-sm text-gray-600">
      Showing <span class="font-semibold">{{ $items->count() }}</span> of <span class="font-semibold">{{ $items->total() }}</span>
    </p>
    <div class="flex gap-1">
      {{ $items->links() }}
    </div>
  </div>
</div>
```

## Avatar & User Components

### User Avatar
```blade
<!-- Avatar Circle -->
<div class="w-10 h-10 rounded-full bg-gradient-to-br from-koperasi-400 to-koperasi-600 flex items-center justify-center text-white font-bold text-sm">
  {{ substr($user->name, 0, 1) }}
</div>

<!-- Avatar Large -->
<div class="w-16 h-16 rounded-full bg-gradient-to-br from-koperasi-400 to-koperasi-600 flex items-center justify-center text-white font-bold text-2xl">
  {{ substr($user->name, 0, 1) }}
</div>

<!-- Member Row -->
<div class="flex items-center">
  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-koperasi-400 to-koperasi-600 flex items-center justify-center text-white font-bold text-sm">
    J
  </div>
  <div class="ml-3">
    <p class="font-semibold text-gray-900">John Doe</p>
    <p class="text-xs text-gray-500">ID: 12345</p>
  </div>
</div>
```

## Alert Messages

### Success Alert
```blade
<div class="p-4 bg-koperasi-50 border border-koperasi-300 text-koperasi-800 rounded-lg flex items-center space-x-3">
  <span class="text-xl">✅</span>
  <span>Success message here</span>
</div>
```

### Error Alert
```blade
<div class="p-4 bg-red-50 border border-red-300 text-red-800 rounded-lg flex items-center space-x-3">
  <span class="text-xl">❌</span>
  <span>Error message here</span>
</div>
```

### Warning Alert
```blade
<div class="p-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg flex items-center space-x-3">
  <span class="text-xl">⚠️</span>
  <span>Warning message here</span>
</div>
```

## Navigation & Sidebar

### Sidebar Link
```blade
<a href="{{ route('dashboard') }}" 
   class="flex items-center px-4 py-3 text-koperasi-100 hover:bg-koperasi-700 rounded-lg transition-colors duration-200 group">
  <span class="text-xl mr-3 group-hover:scale-110 transition-transform">📊</span>
  <span class="font-medium">Dashboard</span>
</a>
```

### Top Navigation
```blade
<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
  <div class="flex items-center justify-between px-6 py-4">
    <div class="flex items-center space-x-4">
      <button class="text-gray-600 hover:text-gray-800 lg:hidden transition-colors">
        <!-- Menu icon -->
      </button>
      <h2 class="text-2xl font-bold text-koperasi-900">Page Title</h2>
    </div>
    <div class="flex items-center space-x-4">
      <p class="text-sm text-gray-600">{{ now()->format('d M Y') }}</p>
    </div>
  </div>
</header>
```

## Utility Classes

### Spacing
```
Padding: p-1 to p-12, px-*, py-*
Margin: m-1 to m-12, mx-*, my-*
Gap: gap-1 to gap-12
```

### Flexbox
```
flex flex-row flex-col
items-start items-center items-end
justify-start justify-center justify-between
gap-1 to gap-12
```

### Grid
```
grid grid-cols-1 grid-cols-2 grid-cols-3 grid-cols-4
gap-4 gap-6
```

### Responsive
```
sm: < 640px
md: 640px - 768px
lg: 768px - 1024px
xl: 1024px - 1280px

Example: md:grid-cols-2 lg:grid-cols-4
```

### Typography
```
text-xs text-sm text-base text-lg text-xl text-2xl text-3xl
font-normal font-medium font-semibold font-bold
text-gray-600 text-koperasi-900
```

### Rounded Corners
```
rounded-lg (8px)
rounded-xl (12px)
rounded-2xl (16px)
rounded-full (50%)
```

### Shadows
```
shadow-sm (subtle)
shadow (default)
shadow-md (medium)
shadow-lg (large)
shadow-xl (extra large)
```

### Opacity
```
opacity-0 opacity-10 opacity-20 ... opacity-100
```

### Transitions
```
transition transition-all
duration-75 duration-100 duration-150 duration-200 duration-300
ease-out ease-in ease-linear
```

---

## Common Patterns

### Search Bar
```blade
<form method="GET" action="{{ route('items.index') }}" class="flex gap-2">
  <input type="text" name="search" value="{{ request('search') }}" 
         placeholder="Search..." 
         class="flex-1 px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-koperasi-500 focus:ring-2 focus:ring-koperasi-100 transition-all">
  <button type="submit" class="px-4 py-2 bg-koperasi-600 text-white rounded-lg hover:bg-koperasi-700 font-medium transition-colors">
    🔍 Search
  </button>
</form>
```

### Action Row
```blade
<div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
  <!-- Left: Search -->
  <form class="flex gap-2 w-full sm:w-auto">
    <!-- Search form -->
  </form>
  
  <!-- Right: Action Button -->
  <a href="{{ route('create') }}" class="px-6 py-2 bg-gradient-to-r from-koperasi-600 to-koperasi-500 text-white rounded-lg hover:from-koperasi-700 hover:to-koperasi-600 font-medium transition-all shadow-md hover:shadow-lg">
    ➕ Add New
  </a>
</div>
```

### Modal Structure
```blade
<div x-data="{ open: false }" @keydown.escape="open = false">
  <!-- Modal Trigger -->
  <button @click="open = true">Open</button>

  <!-- Modal Backdrop -->
  <div x-show="open" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50" @click="open = false">
    <!-- Modal Content -->
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden" @click.stop>
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-900">Modal Title</h3>
      </div>
      <div class="px-6 py-4">
        <!-- Modal content -->
      </div>
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
        <button @click="open = false" class="btn-secondary">Cancel</button>
        <button class="btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>
```

---

## Animation Classes

```
animate-fadeIn           /* Fade in */
animate-slide-up         /* Slide up */
animate-scale-in         /* Scale in */
```

---

## Responsive Grid Examples

### 4 Column Desktop, 2 Column Tablet, 1 Column Mobile
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
  <!-- Items -->
</div>
```

### 2 Column Desktop, 1 Column Tablet/Mobile
```html
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <!-- Items -->
</div>
```

---

**Last Updated**: January 2026  
**Version**: 1.0  
**Status**: Complete & Ready for Use
