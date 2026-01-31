<x-app-layout>

  <style>


/* Početni stil za sve tabove */
.tab-btn {
  border: 2px solid transparent; /* Transparentan okvir kao početni */
  border-radius: 5px; /* Zaobljeni uglovi */
  padding: 8px 16px; /* Prošireni prostor oko teksta */
  transition: border-color 0.3s ease, color 0.3s ease; /* Animacija za glatke promene */
  color: #374151; /* Početna boja teksta za neaktivne tabove */
  position: relative; /* Za pozicioniranje linije ispod taba */
}

/* Hover efekat - plavi okvir pri hoveru */
.tab-btn:hover {
  border-color: #2563eb; /* Plavi okvir pri hoveru */
  color: #2563eb; /* Plavi tekst pri hoveru */
}

/* Neaktivni tab - plavi okvir, tanka linija ispod */
.tab-btn:not(.active) {
  border-color: #2563eb; /* Plavi okvir za neaktivne tabove */
  color: #374151; /* Siva boja teksta za neaktivni tab */
  border-bottom: 2px solid #2563eb; /* Tanka plava linija ispod neaktivnog taba */
}

/* Aktivni tab - plavi okvir i plava linija ispod */
.tab-btn.active {
  border-color: #2563eb; /* Plavi okvir za aktivni tab */
  color: #2563eb; /* Plavi tekst za aktivni tab */
  font-weight: bold; /* Podebljan tekst za aktivni tab */
}

/* Dupla linija ispod aktivnog taba */
.tab-btn.active:after {
  content: '';
  position: absolute;
  bottom: -6px; /* Pomera liniju malo ispod dugmeta */
  left: 0;
  width: 100%; /* Linija pokriva celu širinu dugmeta */
  height: 4px; /* Debljina linije */
  background-color: #2563eb; /* Plava boja linije */
  border-radius: 2px; /* Zaobljeni krajevi linije */
  z-index: 1; /* Osigurava da linija bude iznad drugih elemenata */
}
/* Dugmad pored tabele – koriste ISTI stil kao tabovi */
.side-tab-btn {
  @apply tab-btn;
    border: 0.5px solid #2563eb; /* PLAVI OKVIR UVIJEK */
    border-radius: 5px;
  text-align: left;
  width: 100%;
  padding: 6px 10px;
  font-size: 0.75rem; /* text-xs */
}

/* Aktivno dugme pored tabele */
.side-tab-btn.active {
  color: #2563eb;
  font-weight: bold;
}
  </style>
  <div class="py-4 max-w-7xl mx-auto px-6">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-3">
      <h1 class="text-3xl font-bold text-gray-900">Izvještaji</h1>

      <div class="flex gap-1 bg-gray-50 rounded-lg p-1">
        <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent" data-tab="prepisi">Prepisi</button>
        <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent" data-tab="mobilnost">Mobilnost</button>
      </div>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 px-4 py-3">

      <!-- ================= PREPISI ================= -->
      <div id="tab-content-prepisi" class="tab-content hidden mb-1">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold m-0 p-0">Prepisi</h2>
        </div>

        <!-- FILTRI -->
        <form method="GET" class="mb-4 flex items-center gap-3">
          <div>
            <label class="block text-xs text-gray-600">Godina</label>
            <select name="year" onchange="this.form.submit()" class="border rounded px-2 py-1 pr-8 text-sm w-28 appearance-none bg-no-repeat bg-right">
              <option value="">Sve</option>
              @foreach($prepisYears as $year)
                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-600">Fakultet</label>
            <select name="fakultet" onchange="this.form.submit()" class="border rounded px-2 py-1 pr-8 text-sm w-56 appearance-none bg-no-repeat bg-right">
              <option value="">Sve</option>
              @foreach($fakulteti as $f)
                <option value="{{ $f->id }}" {{ request('fakultet') == $f->id ? 'selected' : '' }}>{{ $f->naziv }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-600">Nivo</label>
            <select name="nivo" onchange="this.form.submit()" class="border rounded px-2 py-1 pr-8 text-sm w-36 appearance-none bg-no-repeat bg-right">
              <option value="">Sve</option>
              @foreach($nivoOptions as $n)
                <option value="{{ $n->id }}" @if(isset($filterNivo) && $filterNivo == $n->id) selected @endif>{{ $n->naziv }}</option>
              @endforeach
            </select>
          </div>

        </form>

        <div class="flex gap-6 mb-6">
          <!-- GODIŠNJE -->
          <div class="w-4/5 h-56 border border-gray-200 rounded-lg overflow-hidden">
            <div class="text-center font-medium text-gray-700 bg-gray-50 border-b border-gray-200 py-2">
              Godišnje
            </div>
            <div class="h-full bg-gray-50 p-4">
              <canvas id="prepisiChart"></canvas>
            </div>
          </div>

          <!-- POL -->
          <div class="w-1/5 h-56 border border-gray-200 rounded-lg overflow-hidden">
            <div class="text-center font-small text-gray-700 bg-gray-50 border-b border-gray-200 py-2">
              Pol
            </div>
            <div class="h-full bg-gray-50 p-4">
              <canvas id="prepisiGenderChart"></canvas>
            </div>
          </div>
        </div>

        <!-- TABELA PREPISI -->
        <div class="flex gap-6 mb-4 items-start">
          
          <!-- LIJEVA STRANA (DUGMAD) -->
          <div class="w-52 flex-shrink-0 bg-gray-50 border border-gray-200 rounded-lg p-3">
            <div class="mb-3 text-sm text-gray-600 font-medium flex items-center">
              Prikaži tabelu po:
            </div>
            <div class="flex flex-col gap-2">
              <button onclick="setPrepisView('godine')" id="btn-prepis-godine" class="side-tab-btn active">Godinama</button>
              <button onclick="setPrepisView('studenti')" id="btn-prepis-studenti" class="side-tab-btn">Studentima</button>
              <button onclick="setPrepisView('fakulteti')" id="btn-prepis-fakulteti" class="side-tab-btn">Fakultetu</button>
            </div>
          </div>

          <!-- DESNA STRANA (TABELE) -->
          <div class="overflow-x-auto flex-1">
            
            <!-- PRIKAZ PO GODINAMA -->
            <div id="view-prepis-godine">
              <table class="table-fixed text-sm w-auto mx-auto border border-gray-200">
                <thead>
                  <tr class="bg-gray-100 h-8">
                    <th class="px-3 py-1 border font-semibold">Godina</th>
                    <th class="px-3 py-1 border font-semibold">Fakultet</th>
                    <th class="px-3 py-1 border text-center font-semibold">Ukupno</th>
                    <th class="px-3 py-1 border text-center font-semibold">Muško</th>
                    <th class="px-3 py-1 border text-center font-semibold">Žensko</th>
                    <th class="px-3 py-1 border text-center font-semibold">%Muško</th>
                    <th class="px-3 py-1 border text-center font-semibold">%Žensko</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($prepisi as $row)
                    <tr class="h-8 hover:bg-gray-50">
                      <td class="px-3 border">{{ $row->year }}</td>
                      <td class="px-3 border">{{ $row->fakultet }}</td>
                      <td class="px-3 border text-center font-medium">{{ $row->total }}</td>
                      <td class="px-3 border text-center">{{ $row->musko ?? 0 }}</td>
                      <td class="px-3 border text-center">{{ $row->zensko ?? 0 }}</td>
                      <td class="px-3 border text-center">{{ round($row->procenat_musko) }}%</td>
                      <td class="px-3 border text-center">{{ round($row->procenat_zensko) }}%</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="px-3 py-4 text-center text-gray-500 border">Nema podataka</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- PRIKAZ PO STUDENTIMA -->
            <div id="view-prepis-studenti" class="hidden">
              <table class="table-fixed text-sm w-auto mx-auto border border-gray-200">
                <thead class="bg-gray-100 h-8">
                  <tr>
                    <th class="px-4 py-2 border text-left font-semibold">Ime i prezime</th>
                    <th class="px-4 py-2 border text-left font-semibold">Fakultet</th>
                    <th class="px-4 py-2 border text-center font-semibold w-24">Godina</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($prepisiByStudent as $s)
                    <tr class="hover:bg-gray-50">
                      <td class="px-4 py-2 border">{{ $s->ime_prezime }}</td>
                      <td class="px-4 py-2 border">{{ $s->fakultet }}</td>
                      <td class="px-4 py-2 border text-center">{{ $s->godina }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="px-4 py-4 border text-center text-gray-500">Nema podataka</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- PRIKAZ PO FAKULTETIMA -->
            <div id="view-prepis-fakulteti" class="hidden">
              <table class="table-fixed text-sm w-auto mx-auto border border-gray-200">
                <thead class="bg-gray-100 h-8">
                  <tr>
                    <th class="px-4 py-2 border text-left font-semibold">Fakultet</th>
                    <th class="px-4 py-2 border text-center font-semibold w-32">Broj studenata</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($prepisiByFakultet as $f)
                    <tr class="hover:bg-gray-50">
                      <td class="px-4 py-2 border">{{ $f->naziv }}</td>
                      <td class="px-4 py-2 border text-center font-medium">{{ $f->count }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="2" class="px-4 py-4 border text-center text-gray-500">Nema podataka</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

          </div>
        </div>

        <!-- DUGME IZVEZI -->
        <div class="flex justify-end">
          <?php $query = http_build_query(request()->except('_token')); ?>
          <a href="{{ route('izvjestaji.export', 'prepisi') }}{{ $query ? '?'.$query : '' }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Izvezi
          </a>
        </div>
      </div>

      <!-- ================= MOBILNOST ================= -->
      <div id="tab-content-mobilnost" class="tab-content hidden mb-1">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold m-0 p-0">Mobilnost</h2>
        </div>

        <!-- FILTERI -->
        <form method="GET" class="mb-4 flex items-center gap-3" id="mobilnostFilterForm">
          <div>
            <label class="block text-xs text-gray-600">Godina</label>
            <select name="year" onchange="this.form.submit()" class="border rounded px-2 py-1 pr-8 text-sm w-28 appearance-none bg-no-repeat bg-right">
              <option value="">Sve</option>
              @foreach($mobilnostYears as $year)
                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-600">Fakultet</label>
            <select name="fakultet" onchange="this.form.submit()" class="border rounded px-2 py-1 pr-8 text-sm w-56 appearance-none bg-no-repeat bg-right">
              <option value="">Sve</option>
              @foreach($fakulteti as $f)
                <option value="{{ $f->id }}" {{ request('fakultet') == $f->id ? 'selected' : '' }}>{{ $f->naziv }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-600">Nivo</label>
            <select name="nivo" onchange="this.form.submit()" class="border rounded px-2 py-1 pr-8 text-sm w-36 appearance-none bg-no-repeat bg-right">
              <option value="">Sve</option>
              @foreach($nivoOptions as $n)
                <option value="{{ $n->id }}" @if(isset($filterNivo) && $filterNivo == $n->id) selected @endif>{{ $n->naziv }}</option>
              @endforeach
            </select>
          </div>

        </form>

<div class="flex gap-6 mb-6">
  <!-- GODISNJE -->
  <div class="w-3/5 h-56 border border-gray-200 rounded-lg overflow-hidden">
    <div class="text-center font-medium text-gray-700 bg-gray-50 border-b border-gray-200 py-2">
      Godišnje
    </div>
    <div class="h-full bg-gray-50 p-4">
      <canvas id="mobilnostChart"></canvas>
    </div>
  </div>

  <!-- POL -->
  <div class="w-1/5 h-56 border border-gray-200 rounded-lg overflow-hidden">
    <div class="text-center font-small text-gray-700 bg-gray-50 border-b border-gray-200 py-2">
      Pol
    </div>
    <div class="h-full bg-gray-50 p-4">
      <canvas id="mobilnostGenderChart"></canvas>
    </div>
  </div>

  <!-- NIV0 -->
  <div class="w-1/5 h-56 border border-gray-200 rounded-lg overflow-hidden">
    <div class="text-center font-medium text-gray-700 bg-gray-50 border-b border-gray-200 py-2">
      Nivo studija
    </div>
    <div class="h-full bg-gray-50 p-4">
      <canvas id="mobilnostNivoChart"></canvas>
    </div>
  </div>

</div> <!-- end of flex container for charts -->

<!-- TABELA MOBILNOST -->
<div class="flex gap-6 mb-4 items-start">

  <!-- LIJEVA STRANA -->
  <div class="w-52 flex-shrink-0 bg-gray-50 border border-gray-200 rounded-lg p-3">
    
    <!-- HEADER -->
    <div class="mb-3 text-sm text-gray-600 font-medium flex items-center">
      Prikaži tabelu po:
    </div>

    <!-- BUTTONS -->
    <div class="flex flex-col gap-2">
      <button
        onclick="setMobView('godine')"
        id="btn-mob-godine"
        class="side-tab-btn active"
      >
        Godinama
      </button>

      <button
        onclick="setMobView('studenti')"
        id="btn-mob-studenti"
        class="side-tab-btn"
      >
        Studentima
      </button>

      <button
        onclick="setMobView('fakulteti')"
        id="btn-mob-fakulteti"
        class="side-tab-btn"
      >
        Fakultetu
      </button>
    </div>
  </div>

  <!-- DESNA STRANA -->
  <div class="overflow-x-auto flex-1">

    <!-- PRAZAN HEADER (PORAVNANJE) -->


    <!-- PRIKAZ PO GODINAMA -->
    <div id="view-mob-godine">
      <table class="table-fixed text-sm w-auto mx-auto border border-gray-200">
        <thead>
          <tr class="bg-gray-100 h-8">
            <th class="py-1 px-3 border text-left font-semibold">Godina</th>
            <th class="py-1 px-3 border text-center w-16">Ukupno</th>
            <th class="py-1 px-3 border text-center w-16">Muško</th>
            <th class="py-1 px-3 border text-center w-16">Žensko</th>
            <th class="py-1 px-3 border text-center w-20">%Muško</th>
            <th class="py-1 px-3 border text-center w-20">%Žensko</th>
            <th class="py-1 px-3 border text-center w-16">Master</th>
            <th class="py-1 px-3 border text-center w-16">Osnovne</th>
          </tr>
        </thead>
        <tbody>
          @forelse($mobilnosti as $row)
            <tr class="h-8 hover:bg-gray-50">
              <td class="py-1 px-3 border">{{ $row->year }}</td>
              <td class="py-1 px-3 border text-center font-medium">{{ $row->total }}</td>
              <td class="py-1 px-3 border text-center">{{ $row->musko }}</td>
              <td class="py-1 px-3 border text-center">{{ $row->zensko }}</td>
              <td class="py-1 px-3 border text-center">{{ round($row->procenat_musko) }}%</td>
              <td class="py-1 px-3 border text-center">{{ round($row->procenat_zensko) }}%</td>
              <td class="py-1 px-3 border text-center">{{ $row->master }}</td>
              <td class="py-1 px-3 border text-center">{{ $row->osnovne }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-3 py-4 text-center text-gray-500 border">
                Nema podataka
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- PRIKAZ PO STUDENTIMA -->
    <div id="view-mob-studenti" class="hidden">
      <table class="table-fixed text-sm w-auto mx-auto border border-gray-200">
        <thead class="bg-gray-100 h-8">
          <tr>
            <th class="px-4 py-2 border text-left font-semibold">Ime i prezime</th>
            <th class="px-4 py-2 border text-left font-semibold">Fakultet</th>
            <th class="px-4 py-2 border text-center font-semibold w-24">Godina</th>
          </tr>
        </thead>
        <tbody>
          @forelse($mobilnostiByStudent as $s)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-2 border">{{ $s->ime_prezime }}</td>
              <td class="px-4 py-2 border">{{ $s->fakultet }}</td>
              <td class="px-4 py-2 border text-center">{{ $s->godina }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-4 py-4 border text-center text-gray-500">
                Nema podataka
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- PRIKAZ PO FAKULTETIMA -->
    <div id="view-mob-fakulteti" class="hidden">
      <table class="table-fixed text-sm w-auto mx-auto border border-gray-200">
        <thead class="bg-gray-100 h-8">
          <tr>
            <th class="px-4 py-2 border text-left font-semibold">Fakultet</th>
            <th class="px-4 py-2 border text-center font-semibold w-32">
              Broj studenata
            </th>
          </tr>
        </thead>
        <tbody>
          @forelse($mobilnostiByFakultet as $f)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-2 border">{{ $f->naziv }}</td>
              <td class="px-4 py-2 border text-center font-medium">{{ $f->count }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="px-4 py-4 border text-center text-gray-500">
                Nema podataka
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>



        <!-- DUGME IZVEZI -->
        <div class="flex justify-end">
          <?php $query = http_build_query(request()->except('_token')); ?>
          <a href="{{ route('izvjestaji.export', 'mobilnost') }}{{ $query ? '?'.$query : '' }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Izvezi
          </a>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ================= TABOVI ================= */
  const tabs = document.querySelectorAll('.tab-btn');
  const contents = document.querySelectorAll('.tab-content');
  let activeTab = sessionStorage.getItem('active-tab') || 'mobilnost';

  function showTab(tab){
    contents.forEach(c => c.classList.add('hidden'));
    document.getElementById('tab-content-' + tab).classList.remove('hidden');

tabs.forEach(b => {
  // Uklonimo prethodnu liniju, ako postoji
  let line = b.querySelector('.active-line');
  if (line) {
    line.remove(); // Uklonimo prethodnu liniju
  }

  // Postavljamo boje i efekat linije ispod taba na osnovu aktivnog taba
  if (b.dataset.tab === tab) {
    // Aktivni tab
    b.classList.add('active'); // Dodajemo aktivnu klasu
    b.style.borderColor = '#2563eb'; // Plavi okvir
    b.style.color = '#2563eb'; // Plavi tekst
    b.style.fontWeight = 'bold'; // Podebljan tekst (opciono)

    // Kreiramo novu liniju ispod aktivnog taba
    let line = document.createElement('div');
    line.classList.add('active-line'); // Dodajemo klasu za referencu
    line.style.position = 'absolute';
    line.style.bottom = '-6px';
    line.style.left = '0';
    line.style.width = '100%';
    line.style.height = '4px';
    line.style.backgroundColor = '#2563eb';
    line.style.borderRadius = '2px';
    line.style.zIndex = '1'; // Obezbeđuje da linija bude iznad drugih elemenata
    b.appendChild(line);
  } else {
    // Neaktivni tab
    b.classList.remove('active'); // Uklanjamo aktivnu klasu
    b.style.borderColor = '#2563eb'; // Plavi okvir za neaktivni tab
    b.style.color = '#374151'; // Siva boja za neaktivni tab
    b.style.fontWeight = 'normal'; // Normalan tekst za neaktivni tab
  }
});



    sessionStorage.setItem('active-tab', tab);
    resizeCharts();
  }

  tabs.forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
  showTab(activeTab);

  /* ================= PREPIS TABLE VIEW SWITCHER ================= */
  window.setPrepisView = function(view) {
    // Hide all views
    document.getElementById('view-prepis-godine').classList.add('hidden');
    document.getElementById('view-prepis-studenti').classList.add('hidden');
    document.getElementById('view-prepis-fakulteti').classList.add('hidden');
    
    // Show selected
    document.getElementById('view-prepis-' + view).classList.remove('hidden');

    // Update buttons style
    ['godine', 'studenti', 'fakulteti'].forEach(v => {
      const btn = document.getElementById('btn-prepis-' + v);
      btn.classList.toggle('active', v === view);
    });
  };

  /* ================= MOBILNOST TABLE VIEW SWITCHER ================= */
  window.setMobView = function(view) {
    // Hide all views
    document.getElementById('view-mob-godine').classList.add('hidden');
    document.getElementById('view-mob-studenti').classList.add('hidden');
    document.getElementById('view-mob-fakulteti').classList.add('hidden');
    
    // Show selected
    document.getElementById('view-mob-' + view).classList.remove('hidden');

    // Update buttons style
['godine', 'studenti', 'fakulteti'].forEach(v => {
  const btn = document.getElementById('btn-mob-' + v);
  btn.classList.toggle('active', v === view);
});
  };

  /* ================= PODACI ================= */
  const prepisiData = @json($prepisi->map(fn($r)=>['year'=>$r->year,'total'=>$r->total]));
  const prepisiGenderData = @json($prepisiGenderData ?? []);
  const mobilnostiData = @json($mobilnosti);
  const mobilnostiGenderData = @json($mobilnostiGenderData ?? []);

  const mobilnostiByNivo = @json($mobilnostiByNivo ?? []);
  const mobilnostiYearData = @json($mobilnostiYearData ?? new stdClass());
  const prepisYearData = @json($prepisYearData ?? new stdClass());
  
  console.log('mobilnostiYearData:', mobilnostiYearData);
  console.log('mobilnostiData:', mobilnostiData);
  console.log('mobilnostiGenderData:', mobilnostiGenderData);
  console.log('mobilnostiByNivo:', mobilnostiByNivo);
  console.log('prepisiGenderData:', prepisiGenderData);
  console.log('prepisYearData:', prepisYearData);

  /* ================= HELPER ================= */
  function initBar(id, labels, datasets){
    return new Chart(document.getElementById(id),{
      type:'bar',
      data:{ labels, datasets },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        scales:{
          x:{ offset:true, grid:{display:false} },
          y:{ beginAtZero:true, ticks:{stepSize:1} }
        },
        plugins:{
          legend:{
            position:'bottom',
            labels: {
              boxWidth: 15,
              padding: 15,
              font: {
                size: 14
              }
            }
          }
        }
      }
    });
  }

  /* ================= GRAFICI ================= */

  /* ================= PREPIS CHARTS ================= */
  
  // Kreiraj "Godišnje" grafik za Prepis sa tooltip-ima
  try {
    // Grupiraj prepise po godini
    const prepisYearMap = {};
    if(prepisiData && Array.isArray(prepisiData)) {
      prepisiData.forEach(d => {
        if(!prepisYearMap[d.year]) {
          prepisYearMap[d.year] = { year: d.year, total: 0 };
        }
        prepisYearMap[d.year].total += d.total || 0;
      });
    }
    
    // Konvertuj u sortirani niz
    const prepisYearDataArray = Object.keys(prepisYearMap).sort().map(year => prepisYearMap[year]);
    
    new Chart(document.getElementById('prepisiChart'), {
      type:'bar',
      data:{
        labels: prepisYearDataArray.map(d => d.year),
        datasets: [
          { label:'Ukupno', data:prepisYearDataArray.map(d => d.total), backgroundColor:'#10b981', borderRadius:6, maxBarThickness:18 }
        ]
      },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        scales:{
          x:{ offset:true, grid:{display:false} },
          y:{ beginAtZero:true, ticks:{stepSize:1} }
        },
        plugins:{
          tooltip:{
            callbacks:{
              afterLabel: function(ctx){
                try {
                  const year = ctx.label;
                  const yearData = prepisYearData[year];
                  if(!yearData) return '';
                  
                  const allStudents = [...(yearData.students_musko || []), ...(yearData.students_zensko || [])];
                  if(!allStudents || allStudents.length === 0) return '';
                  
                  const lines = [];
                  allStudents.slice(0, 15).forEach(name => {
                    lines.push(name);
                  });
                  
                  if(allStudents.length > 6) {
                    lines.push('...');
                  }
                  
                  return lines;
                } catch(e) {
                  console.error('Error in prepis tooltip:', e);
                  return '';
                }
              }
            }
          },
          legend:{
            position:'bottom',
            labels: {
              boxWidth: 15,
              padding: 15,
              font: {
                size: 14
              }
            }
          }
        }
      }
    });
  } catch(e) {
    console.error('Error creating prepisiChart:', e);
  }

  // Kreiraj "Pol" grafik za Prepis sa tooltip-ima
  try {
    new Chart(document.getElementById('prepisiGenderChart'), {
      type:'doughnut',
      data:{
        labels: (prepisiGenderData && Array.isArray(prepisiGenderData)) ? 
                prepisiGenderData.map(d => d.label || (d.pol === 'musko' ? 'Muško' : 'Žensko')) : 
                ['Muško','Žensko'],
        datasets:[{
          data: (prepisiGenderData && Array.isArray(prepisiGenderData)) ? 
                prepisiGenderData.map(d => d.total) : 
                [],
          backgroundColor:['#2563eb','#ef4444']
        }]
      },
      options:{
        responsive:true,
        maintainAspectRatio:true,
        aspectRatio:1.2,
        plugins:{
          tooltip:{
            callbacks:{
              label: function(ctx){
                try {
                  const d = prepisiGenderData[ctx.dataIndex];
                  if(!d) return '';
                  
                  const lines = [`Ukupno: ${d.total}`];
                  
                  if(d.students && Array.isArray(d.students)) {
                    d.students.slice(0, 15).forEach(name => {
                      lines.push(name);
                    });
                    
                    if(d.students.length > 6) {
                      lines.push('...');
                    }
                  }
                  
                  return lines;
                } catch(e) {
                  console.error('Error in prepis gender tooltip:', e);
                  return '';
                }
              }
            }
          },
          legend:{
            position:'bottom',
            labels: {
              boxWidth: 15,
              padding: 15,
              font: {
                size: 10
              }
            }
          }
        }
      }
    });
  } catch(e) {
    console.error('Error creating prepisiGenderChart:', e);
  }

// Kreiraj custom "Godišnje" grafik sa tooltip-ima
try {
  // Grupiraj mobilnosti po godini
  const yearMap = {};
  if(mobilnostiData && Array.isArray(mobilnostiData)) {
    mobilnostiData.forEach(d => {
      if(!yearMap[d.year]) {
        yearMap[d.year] = { year: d.year, musko: 0, zensko: 0 };
      }
      yearMap[d.year].musko += d.musko || 0;
      yearMap[d.year].zensko += d.zensko || 0;
    });
  }
  
  // Konvertuj u sortirani niz
  const yearData = Object.keys(yearMap).sort().map(year => yearMap[year]);
  
  const canvasEl = document.getElementById('mobilnostChart');
  console.log('Canvas mobilnostChart element:', canvasEl);
  
  const labels = yearData.map(d => d.year);
  const musko_data = yearData.map(d => d.musko);
  const zensko_data = yearData.map(d => d.zensko);
  
  console.log('Creating mobilnostChart with labels:', labels);
  
  new Chart(canvasEl, {
    type:'bar',
    data:{
      labels: labels,
      datasets: [
        { label:'Muško', data:musko_data, backgroundColor:'#2563eb', borderRadius:6, maxBarThickness:18 },
        { label:'Žensko', data:zensko_data, backgroundColor:'#ef4444', borderRadius:6, maxBarThickness:18 }
      ]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{
        x:{ offset:true, grid:{display:false}, stacked: true },
        y:{ beginAtZero:true, ticks:{stepSize:1}, stacked: true }
      },
      plugins:{
        tooltip:{
          callbacks:{
            afterLabel: function(ctx){
              try {
                const year = ctx.label;
                console.log('Tooltip year:', year);
                console.log('mobilnostiYearData keys:', Object.keys(mobilnostiYearData));
                console.log('mobilnostiYearData:', mobilnostiYearData);
                
                const yearData = mobilnostiYearData[year];
                console.log('yearData for', year, ':', yearData);
                
                if(!yearData) {
                  console.log('No yearData found for year:', year);
                  return '';
                }
                
                const isMale = ctx.datasetIndex === 0;
                const students = isMale ? yearData.students_musko : yearData.students_zensko;
                console.log('Students for', isMale ? 'musko' : 'zensko', ':', students);
                
                if(!students || !Array.isArray(students)) {
                  console.log('Students is not an array or is null');
                  return '';
                }
                
                const lines = [];
                students.slice(0, 15).forEach(name => {
                  lines.push(name);
                });
                
                if(students.length > 6) {
                  lines.push('...');
                }
                
                console.log('Returning lines:', lines);
                return lines;
              } catch(e) {
                console.error('Error in tooltip:', e);
                return '';
              }
            }
          }
        },
        legend:{
          position:'bottom',
          labels: {
            boxWidth: 15,
            padding: 15,
            font: {
              size: 14
            }
          }
        }
      }
    }
  });
  console.log('mobilnostChart created successfully');
} catch(e) {
  console.error('Error creating mobilnostChart:', e);
}

new Chart(document.getElementById('mobilnostGenderChart'), {
  type: 'doughnut',
  data: {
    labels: (mobilnostiGenderData && Array.isArray(mobilnostiGenderData)) ? mobilnostiGenderData.map(d => d.label) : [],
    datasets: [{
      data: (mobilnostiGenderData && Array.isArray(mobilnostiGenderData)) ? mobilnostiGenderData.map(d => d.total) : [],
      backgroundColor: ['#2563eb', '#ef4444']
    }]
  },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 1.4,
      plugins: {
        tooltip: {
          callbacks: {
            label: function (ctx) {
              const d = mobilnostiGenderData[ctx.dataIndex];
              if(!d) return '';

              const lines = [
                `Ukupno: ${d.total}`,
              ];

              if(d.students && Array.isArray(d.students)) {
                d.students.slice(0, 15).forEach(name => {
                  lines.push(name);
                });

                if (d.students.length > 6) {
                  lines.push('...');
                }
              }

              return lines;
            }
          }
        },
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 15,
            padding: 25,
            font: {
              size: 11
            }
          }
        }
      }
    }
});
/* ===== MOBILNOST → NIV0 STUDIJA ===== */
try {
  new Chart(document.getElementById('mobilnostNivoChart'), {
    type:'bar',
    data:{
      labels: ['Osnovne','Master'],
      datasets: [{
        label:'Brojčano',
        data: ['Osnovne','Master'].map(l => {
          const nivoItem = (mobilnostiByNivo && Array.isArray(mobilnostiByNivo) && mobilnostiByNivo.find) ? mobilnostiByNivo.find(n => n.label === l) : null;
          return nivoItem ? nivoItem.total : 0;
        }),
        backgroundColor: ['#10b981', '#f59e0b'],
        borderRadius: 6,
        maxBarThickness: 18
      }]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{
        x:{ offset:true, grid:{display:false}, stacked: true },
        y:{ beginAtZero:true, ticks:{stepSize:1}, stacked: true }
      },
      plugins:{
        tooltip:{
          callbacks:{
            afterLabel: function(ctx){
              try {
                const label = ctx.label;
                if(!mobilnostiByNivo || !Array.isArray(mobilnostiByNivo) || !mobilnostiByNivo.find) return '';
                const nivoItem = mobilnostiByNivo.find(n => n.label === label);
                if(!nivoItem || !nivoItem.students) return '';
                
                const lines = [];
                nivoItem.students.slice(0, 15).forEach(name => {
                  lines.push(name);
                });
                
                if(nivoItem.students.length > 6) {
                  lines.push('...');
                }
                
                return lines;
              } catch(e) {
                console.error('Error in nivo tooltip:', e);
                return '';
              }
            }
          }
        },
        legend:{
          display: true,
          position: 'bottom',
          labels: {
            boxWidth: 3,
            padding: 5,
            font: {
              size: 10
            }
          }
        }
      }
    }
  });
  console.log('mobilnostNivoChart created successfully');
} catch(e) {
  console.error('Error creating mobilnostNivoChart:', e);
}
  /* ================= RESIZE ================= */
  function resizeCharts(){
    [
      'prepisiChart',
      'prepisiGenderChart',
      'mobilnostChart',
      'mobilnostGenderChart',
      'mobilnostNivoChart'
    ].forEach(id=>{
      const c = Chart.getChart(id);
      if(c) {
        c.resize();
        c.update();
      }
    });
  }

});
</script>