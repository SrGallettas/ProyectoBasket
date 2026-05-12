@extends('layouts.admin')

@section('contenido_admin')
<div class="admin-live-board">
    <div class="admin-live-board-header">
        <div>
            <p class="admin-live-kicker">Mesa de anotación</p>
            <h1>{{ $partido->equipoLocal->nombre }} vs {{ $partido->equipoVisitante->nombre }}</h1>
            <p class="admin-live-meta">
                {{ \Carbon\Carbon::parse($partido->fecha_hora)->format('d/m/Y H:i') }} · {{ $partido->lugar }} · {{ $partido->categoria }}
            </p>
        </div>

        <div class="admin-live-board-actions">
            <form action="{{ route('partidos.anotacion.deshacer', $partido) }}" method="POST">
                @csrf
                <button type="submit" class="admin-live-secondary-button">Deshacer última</button>
            </form>

            <form action="{{ route('partidos.anotacion.confirmar', $partido) }}" method="POST">
                @csrf
                <button type="submit" class="admin-live-primary-button">Salir de la anotación</button>
            </form>
        </div>
    </div>

    @if(session('mensaje'))
        <div class="alerta-exito">
            <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
        </div>
    @endif

    @if($errors->any())
        <div class="cuadro-errores-admin">
            <strong>Algo ha fallado:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-live-scoreboard">
        <div class="admin-live-score-team">
            <span class="admin-live-team-label">Local</span>
            <strong>{{ $partido->equipoLocal->nombre }}</strong>
            <div class="admin-live-score">{{ $resumen['local']['puntos'] }}</div>
        </div>

        <div class="admin-live-score-center">
            <div class="admin-live-period-display">{{ [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'PR'][$periodoActivo] }}</div>
            <form action="{{ route('partidos.anotacion.periodo', $partido) }}" method="POST" class="admin-live-period-form">
                @csrf
                @foreach([1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'PR'] as $periodo => $label)
                    <button type="submit" name="periodo" value="{{ $periodo }}" class="admin-live-period-button {{ $periodoActivo === $periodo ? 'is-active' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>
        </div>

        <div class="admin-live-score-team">
            <span class="admin-live-team-label">Visitante</span>
            <strong>{{ $partido->equipoVisitante->nombre }}</strong>
            <div class="admin-live-score">{{ $resumen['visitante']['puntos'] }}</div>
        </div>
    </div>

    <div class="admin-live-main-grid">
        @php
            $teamConfigs = [
                'local' => ['title' => $partido->equipoLocal->nombre, 'summary' => $resumen['local']],
                'visitante' => ['title' => $partido->equipoVisitante->nombre, 'summary' => $resumen['visitante']],
            ];

            $actionGroups = [
                'Puntos' => ['ft_made', 'ft_miss', 'two_made', 'two_miss', 'three_made', 'three_miss'],
                'Juego' => ['reb_of', 'reb_def', 'ast', 'stl', 'tov', 'blk', 'pf', 'tf'],
            ];

            $statLabels = [
                'fga' => 'TC intentados',
                'fgm' => 'TC anotados',
                't2a' => 'T2 intentados',
                't2m' => 'T2 anotados',
                't3a' => 'T3 intentados',
                't3m' => 'T3 anotados',
                'fta' => 'TL intentados',
                'ftm' => 'TL anotados',
                'reb_of' => 'Rebotes of.',
                'reb_def' => 'Rebotes def.',
                'ast' => 'Asistencias',
                'stl' => 'Robos',
                'tov' => 'Pérdidas',
                'blk' => 'Tapones',
                'pf' => 'Faltas',
                'tf' => 'Técnicas',
            ];
        @endphp

        @foreach($teamConfigs as $lado => $config)
            <section class="admin-live-team-panel">
                <div class="admin-live-team-header">
                    <div>
                        <span class="admin-live-team-side">{{ ucfirst($lado) }}</span>
                        <h2>{{ $config['title'] }}</h2>
                    </div>
                    <span class="admin-live-team-points">{{ $config['summary']['puntos'] }} pts</span>
                </div>

                <div class="admin-live-quarter-grid">
                    @foreach($config['summary']['cuartos'] as $periodo => $puntos)
                        <div class="admin-live-quarter-chip {{ $periodoActivo === $periodo ? 'is-active' : '' }}">
                            <span>{{ [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'PR'][$periodo] }}</span>
                            <strong>{{ $puntos }}</strong>
                        </div>
                    @endforeach
                </div>

                @foreach($actionGroups as $groupTitle => $groupActions)
                    <div class="admin-live-action-group">
                        <h3>{{ $groupTitle }}</h3>
                        <div class="admin-live-action-grid">
                            @foreach($groupActions as $actionKey)
                                <form action="{{ route('partidos.anotacion.accion', $partido) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="lado" value="{{ $lado }}">
                                    <input type="hidden" name="accion" value="{{ $actionKey }}">
                                    <button type="submit" class="admin-live-action-button">{{ $acciones[$actionKey]['label'] }}</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="admin-live-stats-grid">
                    @foreach($statLabels as $statKey => $label)
                        <div class="admin-live-stat-card">
                            <span>{{ $label }}</span>
                            <strong>{{ $config['summary']['stats'][$statKey] }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <div class="admin-live-history-card">
        <div class="admin-live-history-header">
            <h3>Últimas acciones</h3>
            <span>Se guardan al instante</span>
        </div>

        @if(!empty($history))
            <div class="admin-live-history-list">
                @foreach(collect($history)->take(8) as $item)
                    <div class="admin-live-history-item">
                        <strong>{{ strtoupper(substr($item['lado'], 0, 1)) }} · {{ [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'PR'][$item['periodo']] }}</strong>
                        <span>{{ $acciones[$item['accion']]['label'] }}</span>
                        <em>{{ $item['timestamp'] }}</em>
                    </div>
                @endforeach
            </div>
        @else
            <p class="admin-live-history-empty">Todavía no se ha registrado ninguna acción en este partido.</p>
        @endif
    </div>
</div>
@endsection
