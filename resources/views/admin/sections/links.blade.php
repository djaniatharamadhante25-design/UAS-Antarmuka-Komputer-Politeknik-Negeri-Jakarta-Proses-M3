@php
    $f = $filters;
    $from = $pg['total'] ? ($pg['page'] - 1) * $pg['per'] + 1 : 0;
    $to = $from ? $from + count($pg['rows']) - 1 : 0;
    $linksFilter = array_filter($f);
    $hasSelectedMedia = $edit && !empty($edit->external_id);
@endphp

<div class="adm-toolbar">
  <form class="adm-filter" method="get" action="{{ route('admin.section', ['section' => 'links']) }}" role="search">
    <label class="adm-search">
      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      <input type="search" name="q" value="{{ $f['q'] }}" placeholder="Cari judul anime, MAL ID, provider, atau URL" aria-label="Cari link">
    </label>
    <select name="type" aria-label="Filter tipe">
      <option value="">Anime dan manga</option>
      <option value="anime" {{ $f['type'] === 'anime' ? 'selected' : '' }}>Anime</option>
      <option value="manga" {{ $f['type'] === 'manga' ? 'selected' : '' }}>Manga</option>
    </select>
    <button class="btn btn-sm btn-outline-light" type="submit">Terapkan</button>
    @if ($f['q'] !== '' || $f['type'] !== '')
        <a class="adm-reset" href="{{ route('admin.section', ['section' => 'links']) }}">Reset</a>
    @endif
  </form>
</div>

<details class="adm-form" {{ $edit ? 'open' : '' }}>
  <summary class="btn btn-primary btn-sm">
    <i class="fa-solid {{ $edit ? 'fa-pen' : 'fa-plus' }}" aria-hidden="true"></i> 
    {{ $edit ? 'Edit link #' . (int) $edit->id . (!empty($edit->title) ? ': ' . $edit->title : '') : 'Tambah link nonton/baca' }}
  </summary>

  <form method="post" action="{{ route('admin.action') }}" class="adm-fields" id="watchLinkForm">
    @csrf
    <input type="hidden" name="back" value="{{ App\Support\AdminPresenter::currentQuery(request()) }}">
    <input type="hidden" name="action" value="admin_watch_save">
    <input type="hidden" name="watch_id" value="{{ (int) ($edit->id ?? 0) }}">
    <input type="hidden" name="title" id="inputTitle" value="{{ $edit->title ?? '' }}">
    <input type="hidden" name="image_url" id="inputImageUrl" value="{{ $edit->image_url ?? '' }}">

    <!-- 1. Anime / Manga Live Search Picker -->
    <div class="adm-picker-section wide">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="m-0 fw-bold"><i class="fa-solid fa-film me-1 text-primary"></i> Pilih Anime / Manga</label>
        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted" id="toggleManualId" style="font-size: 11px;">
          <i class="fa-solid fa-keyboard me-1"></i> <span id="manualToggleText">Input ID Manual</span>
        </button>
      </div>

      <!-- Live Search Box -->
      <div class="adm-picker-search-box" id="pickerSearchBox" style="{{ $hasSelectedMedia ? 'display: none;' : '' }}">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-magnifying-glass"></i></span>
          <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="pickerSearchInput" placeholder="Ketik nama anime atau manga (misal: Attack on Titan, Solo Leveling, One Piece)..." autocomplete="off">
          <button type="button" class="btn btn-primary btn-sm px-3" id="btnSearchPicker">Cari</button>
        </div>
        <div id="pickerResults" class="adm-picker-dropdown" style="display: none;"></div>
      </div>

      <!-- Selected Media Preview -->
      <div id="selectedPreview" class="adm-selected-card" style="{{ $hasSelectedMedia ? '' : 'display: none;' }}">
        <img id="previewImg" src="{{ $edit->image_url ?? 'https://placehold.co/100x150/111827/ffffff?text=Cover' }}" alt="Preview" class="adm-preview-thumb">
        <div class="adm-preview-detail">
          <strong id="previewTitle" class="adm-preview-title">{{ $edit->title ?? ($edit ? 'Judul #' . $edit->external_id : '') }}</strong>
          <div class="adm-preview-meta">
            <span class="badge bg-primary" id="previewBadge">{{ ucfirst($edit->media_type ?? 'anime') }}</span>
            <span class="text-secondary small">MAL ID: <b id="previewId" class="text-white">{{ $edit->external_id ?? '' }}</b></span>
          </div>
        </div>
        <button type="button" class="btn btn-outline-light btn-sm ms-auto" id="btnChangeMedia" style="font-size: 11px;">
          <i class="fa-solid fa-arrows-rotate me-1"></i> Ganti Judul
        </button>
      </div>

      <!-- Fallback / Manual MAL ID & Type -->
      <div id="manualIdRow" style="display: none;" class="mt-2 pt-2 border-top border-secondary">
        <div class="row g-2">
          <div class="col-md-6">
            <label class="small text-secondary mb-1">MAL ID (Angka di URL MyAnimeList)
              <input type="number" min="1" name="external_id" id="inputExternalId" required value="{{ (int) ($edit->external_id ?? 0) ?: '' }}" class="form-control form-control-sm bg-dark text-white border-secondary">
            </label>
          </div>
          <div class="col-md-6">
            <label class="small text-secondary mb-1">Tipe Media
              <select name="media_type" id="selectMediaType" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="anime" {{ ($edit->media_type ?? 'anime') === 'anime' ? 'selected' : '' }}>Anime</option>
                <option value="manga" {{ ($edit->media_type ?? '') === 'manga' ? 'selected' : '' }}>Manga</option>
              </select>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Provider Selection with Quick Pills -->
    <div class="wide">
      <label>Nama Provider / Platform
        <input name="provider" id="inputProvider" maxlength="100" required placeholder="Contoh: Crunchyroll, Netflix, Bilibili..." value="{{ $edit->provider ?? '' }}">
      </label>
      <div class="adm-quick-providers">
        <span class="small text-muted me-1">Pintasan:</span>
        <button type="button" class="adm-pill-btn" data-provider="Crunchyroll">Crunchyroll</button>
        <button type="button" class="adm-pill-btn" data-provider="Netflix">Netflix</button>
        <button type="button" class="adm-pill-btn" data-provider="Bilibili / Bstation">Bstation</button>
        <button type="button" class="adm-pill-btn" data-provider="Muse Asia">Muse Asia</button>
        <button type="button" class="adm-pill-btn" data-provider="Ani-One Asia">Ani-One</button>
        <button type="button" class="adm-pill-btn" data-provider="Disney+ Hotstar">Disney+</button>
        <button type="button" class="adm-pill-btn" data-provider="Prime Video">Prime Video</button>
        <button type="button" class="adm-pill-btn" data-provider="Vidio">Vidio</button>
        <button type="button" class="adm-pill-btn" data-provider="iQIYI">iQIYI</button>
        <button type="button" class="adm-pill-btn" data-provider="MangaPlus">MangaPlus</button>
      </div>
    </div>

    <!-- 3. Link URL -->
    <label class="wide">URL Platform Nonton / Baca
      <input type="url" name="url" maxlength="1000" required placeholder="https://..." value="{{ $edit->url ?? '' }}">
    </label>

    <div class="adm-fields-actions">
      <button class="btn btn-primary btn-sm" type="submit">
        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Link
      </button>
      @if ($edit)
          <a class="btn btn-outline-light btn-sm" href="{{ route('admin.section', $linksFilter + ['section' => 'links']) }}">Batal</a>
      @endif
    </div>
  </form>
</details>

<div class="adm-card adm-card-flush">
  @if (count($pg['rows']) === 0)
    <p class="adm-empty">{{ ($f['q'] !== '' || $f['type'] !== '') ? 'Tidak ada link yang cocok.' : 'Belum ada link. Pilih “Tambah link nonton/baca” untuk menautkan anime atau manga ke platform resminya.' }}</p>
  @else
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Judul & Media</th>
          <th>Provider</th>
          <th>URL</th>
          <th>Ditambahkan</th>
          <th class="end"><span class="visually-hidden">Aksi</span></th>
        </tr>
      </thead>
      <tbody>
      @foreach ($pg['rows'] as $row)
        @php
            $rowTitle = !empty($row->title) ? $row->title : 'Judul #' . (int) $row->external_id;
            $rowImage = !empty($row->image_url) ? $row->image_url : null;
        @endphp
        <tr>
          <td data-label="Judul & Media">
            <div class="adm-media-cell">
              <div class="adm-media-thumb">
                @if ($rowImage)
                  <img src="{{ $rowImage }}" alt="{{ $rowTitle }}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                  <div class="adm-thumb-placeholder" style="display: none;"><i class="fa-solid fa-film"></i></div>
                @else
                  <div class="adm-thumb-placeholder"><i class="fa-solid fa-film"></i></div>
                @endif
              </div>
              <div class="adm-media-info">
                <strong class="adm-media-title" title="{{ $rowTitle }}">{{ $rowTitle }}</strong>
                <div class="adm-media-sub">
                  <a class="adm-idlink" href="{{ App\Support\OtakuHelper::mediaDetailUrl((string) $row->media_type, (int) $row->external_id, 0) }}" target="_blank" rel="noopener" title="Buka halaman detail">
                    MAL #{{ (int) $row->external_id }} <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                  </a>
                  <span class="adm-badge">{{ ucfirst((string) $row->media_type) }}</span>
                </div>
              </div>
            </div>
          </td>
          <td data-label="Provider">
            <span class="adm-provider-badge">
              <i class="fa-solid fa-play me-1 text-primary"></i> <strong>{{ $row->provider }}</strong>
            </span>
          </td>
          <td data-label="URL" class="adm-url">
            <a href="{{ $row->url }}" target="_blank" rel="noopener noreferrer" title="{{ $row->url }}">
              <i class="fa-solid fa-arrow-up-right-from-square me-1" style="font-size: 10px;"></i>
              {{ (string) (parse_url((string) $row->url, PHP_URL_HOST) . (parse_url((string) $row->url, PHP_URL_PATH) ?: '')) }}
            </a>
          </td>
          <td data-label="Ditambahkan">
            {{ $row->username ?? '—' }}
            <small>{{ App\Support\AdminPresenter::ago((string) $row->created_at) }}</small>
          </td>
          <td class="end">
            <div class="adm-actions">
              <a class="btn btn-sm btn-outline-light" href="{{ route('admin.section', ['section' => 'links', 'edit' => (int) $row->id] + $linksFilter) }}">
                <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
              </a>
              <form method="post" action="{{ route('admin.action') }}" data-confirm="Hapus link {{ $row->provider }} untuk {{ $rowTitle }}?">
                @csrf
                <input type="hidden" name="back" value="{{ App\Support\AdminPresenter::currentQuery(request()) }}">
                <input type="hidden" name="action" value="admin_watch_delete">
                <input type="hidden" name="watch_id" value="{{ (int) $row->id }}">
                <button class="btn btn-sm btn-outline-danger" type="submit">
                  <i class="fa-solid fa-trash" aria-hidden="true"></i> Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  <div class="adm-tablefoot">
    <span>Menampilkan {{ $from }}–{{ $to }} dari {{ (int) $pg['total'] }} link</span>
    {!! App\Support\AdminPresenter::pager($pg, ['section' => 'links'] + $linksFilter) !!}
  </div>
  @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('pickerSearchInput');
  const btnSearch = document.getElementById('btnSearchPicker');
  const resultsBox = document.getElementById('pickerResults');
  const searchBox = document.getElementById('pickerSearchBox');
  const previewBox = document.getElementById('selectedPreview');
  const btnChange = document.getElementById('btnChangeMedia');
  const toggleManual = document.getElementById('toggleManualId');
  const manualRow = document.getElementById('manualIdRow');
  const manualText = document.getElementById('manualToggleText');
  
  const inputExtId = document.getElementById('inputExternalId');
  const selectType = document.getElementById('selectMediaType');
  const inputTitle = document.getElementById('inputTitle');
  const inputImg = document.getElementById('inputImageUrl');
  
  const previewImg = document.getElementById('previewImg');
  const previewTitle = document.getElementById('previewTitle');
  const previewId = document.getElementById('previewId');
  const previewBadge = document.getElementById('previewBadge');

  // Quick provider pill buttons
  document.querySelectorAll('.adm-pill-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const providerInput = document.getElementById('inputProvider');
      if (providerInput) {
        providerInput.value = btn.dataset.provider;
        providerInput.focus();
      }
    });
  });

  // Toggle manual ID input
  if (toggleManual && manualRow) {
    toggleManual.addEventListener('click', () => {
      const isHidden = manualRow.style.display === 'none';
      manualRow.style.display = isHidden ? 'block' : 'none';
      manualText.textContent = isHidden ? 'Sembunyikan ID Manual' : 'Input ID Manual';
    });
  }

  // Change selected anime
  if (btnChange) {
    btnChange.addEventListener('click', () => {
      previewBox.style.display = 'none';
      searchBox.style.display = 'block';
      if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
      }
    });
  }

  // Media Search fetcher
  let searchTimer = null;
  const doSearch = async () => {
    const q = (searchInput?.value || '').trim();
    if (q.length < 2) {
      resultsBox.style.display = 'none';
      return;
    }

    resultsBox.style.display = 'block';
    resultsBox.innerHTML = '<div class="adm-picker-loading"><i class="fa-solid fa-spinner fa-spin me-2"></i> Mencari judul...</div>';

    try {
      const type = selectType ? selectType.value : 'anime';
      const url = `{{ route('admin.search_media') }}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const data = await res.json();

      if (!data || data.length === 0) {
        resultsBox.innerHTML = '<div class="adm-picker-empty">Tidak ada anime/manga yang cocok. Coba kata kunci lain atau gunakan input ID manual.</div>';
        return;
      }

      let html = '<div class="adm-picker-list">';
      data.forEach(item => {
        const cover = item.image_url || 'https://placehold.co/80x120/111827/ffffff?text=Cover';
        const yearStr = item.year ? ` · ${item.year}` : '';
        const scoreStr = item.score ? ` · ★ ${item.score}` : '';
        html += `
          <div class="adm-picker-item" data-json="${encodeURIComponent(JSON.stringify(item))}">
            <img src="${cover}" alt="${item.title}" class="adm-picker-item-thumb">
            <div class="adm-picker-item-info">
              <strong class="adm-picker-item-title">${item.title}</strong>
              <div class="adm-picker-item-meta">
                <span class="badge bg-secondary">${item.format || item.media_type}</span>
                <span>MAL #${item.mal_id}${yearStr}${scoreStr}</span>
              </div>
            </div>
          </div>
        `;
      });
      html += '</div>';
      resultsBox.innerHTML = html;

      // Attach click events on results
      resultsBox.querySelectorAll('.adm-picker-item').forEach(itemEl => {
        itemEl.addEventListener('click', () => {
          const item = JSON.parse(decodeURIComponent(itemEl.dataset.json));
          
          inputExtId.value = item.mal_id;
          if (selectType) selectType.value = item.media_type;
          if (inputTitle) inputTitle.value = item.title;
          if (inputImg) inputImg.value = item.image_url || '';

          previewImg.src = item.image_url || 'https://placehold.co/100x150/111827/ffffff?text=Cover';
          previewTitle.textContent = item.title;
          previewId.textContent = item.mal_id;
          previewBadge.textContent = item.format || item.media_type.toUpperCase();

          resultsBox.style.display = 'none';
          searchBox.style.display = 'none';
          previewBox.style.display = 'flex';
        });
      });
    } catch (err) {
      resultsBox.innerHTML = '<div class="adm-picker-empty text-danger">Gagal memuat data pencarian. Silakan coba lagi.</div>';
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(doSearch, 350);
    });
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(searchTimer);
        doSearch();
      }
    });
  }

  if (btnSearch) {
    btnSearch.addEventListener('click', (e) => {
      e.preventDefault();
      clearTimeout(searchTimer);
      doSearch();
    });
  }

  // Close dropdown on outside click
  document.addEventListener('click', (e) => {
    if (resultsBox && !resultsBox.contains(e.target) && !searchBox.contains(e.target)) {
      resultsBox.style.display = 'none';
    }
  });
});
</script>
@endpush
