@extends('layouts.admin')

@section('title', 'Manajemen Kategori - Imersa Affiliate')
@section('page_title', 'Manajemen Kategori Elektronik')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Left Column: Add Category Form (5 cols) -->
    <div class="lg:col-span-4 space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-orange-600"></i> Tambah Kategori Baru
            </h2>

            <form action="{{ route('admin.categories.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="cat_name" name="name" value="{{ old('name') }}" required
                        placeholder="Contoh: Aksesoris Komputer"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500"
                        oninput="generateCatSlug(this.value)">
                    @error('name')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-xs font-bold text-slate-700 mb-1">
                        Slug (Unik) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="cat_slug" name="slug" value="{{ old('slug') }}" required
                        placeholder="aksesoris-komputer"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('slug')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="parent_id" class="block text-xs font-bold text-slate-700 mb-1">Kategori Induk (Opsional)</label>
                    <select id="parent_id" name="parent_id"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">-- Kategori Utama (Tanpa Induk) --</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea id="description" name="description" rows="2"
                        placeholder="Keterangan cakupan produk dalam kategori ini..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="sort_order" class="block text-xs font-bold text-slate-700 mb-1">Urutan Tampil</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', '0') }}" min="0"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select name="is_active" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-600/30 transition-all cursor-pointer">
                        Simpan Kategori Baru
                    </button>
                </div>
            </form>
        </div>

        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-900 leading-relaxed">
            <strong class="font-bold block mb-1">Aturan Invariant Kategori (BR-32):</strong>
            Kategori yang masih memiliki produk berstatus <strong>aktif</strong> tidak dapat dinonaktifkan atau dihapus sebelum produk-produk tersebut dinonaktifkan atau dipindahkan ke kategori aktif lain.
        </div>
    </div>

    <!-- Right Column: Categories List & Tree Table (8 cols) -->
    <div class="lg:col-span-8 space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Kategori Elektronik</h2>
                    <p class="text-xs text-slate-500">Struktur taksonomi produk PT Imersa Solusi Teknologi</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    Total: {{ $categories->total() }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-6">Nama & Slug</th>
                            <th class="py-3 px-4">Induk</th>
                            <th class="py-3 px-4 text-center">Urutan</th>
                            <th class="py-3 px-4 text-center">Produk Aktif</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $category)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-6 font-semibold text-slate-800">
                                    <div class="flex items-center gap-2">
                                        @if($category->parent_id)
                                            <span class="text-slate-300 font-mono pl-2">&bull;</span>
                                        @endif
                                        <div>
                                            <span class="text-slate-900 font-bold">{{ $category->name }}</span>
                                            <span class="block text-[10px] text-slate-400 font-mono">/{{ $category->slug }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $category->parent->name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-600 font-mono">
                                    {{ $category->sort_order }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $category->products_count > 0 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $category->products_count }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($category->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Modal Trigger -->
                                        <button type="button" onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ $category->slug }}', '{{ $category->parent_id }}', '{{ addslashes($category->description ?? '') }}', {{ $category->is_active ? 1 : 0 }}, {{ $category->sort_order }})"
                                            class="p-1.5 bg-orange-50 hover:bg-orange-600 text-orange-600 hover:text-white rounded-lg transition-colors cursor-pointer" title="Edit Kategori">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>

                                        <!-- Delete Form -->
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan kategori ini? Pastikan tidak ada produk aktif yang terkait.');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-lg transition-colors cursor-pointer" title="Hapus Kategori">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada kategori elektronik terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Category Modal Dialog -->
<div id="edit-cat-modal" class="fixed inset-0 bg-slate-900/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Perbarui Kategori Elektronik</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form id="edit-cat-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_name" class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori</label>
                <input type="text" id="edit_name" name="name" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label for="edit_slug" class="block text-xs font-bold text-slate-700 mb-1">Slug (Unik)</label>
                <input type="text" id="edit_slug" name="slug" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label for="edit_parent_id" class="block text-xs font-bold text-slate-700 mb-1">Kategori Induk</label>
                <select id="edit_parent_id" name="parent_id"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">-- Kategori Utama (Tanpa Induk) --</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="edit_description" class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                <textarea id="edit_description" name="description" rows="2"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_sort_order" class="block text-xs font-bold text-slate-700 mb-1">Urutan Tampil</label>
                    <input type="number" id="edit_sort_order" name="sort_order" min="0"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label for="edit_is_active" class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select id="edit_is_active" name="is_active" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-600/30">
                    Perbarui Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function generateCatSlug(text) {
        const slug = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        document.getElementById('cat_slug').value = slug;
    }

    function openEditModal(id, name, slug, parentId, desc, isActive, sortOrder) {
        const form = document.getElementById('edit-cat-form');
        form.action = `/admin/categories/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_slug').value = slug;
        document.getElementById('edit_parent_id').value = parentId || '';
        document.getElementById('edit_description').value = desc || '';
        document.getElementById('edit_is_active').value = isActive ? '1' : '0';
        document.getElementById('edit_sort_order').value = sortOrder || 0;

        document.getElementById('edit-cat-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-cat-modal').classList.add('hidden');
    }
</script>
@endsection
