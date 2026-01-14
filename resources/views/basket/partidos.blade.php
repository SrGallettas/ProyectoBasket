{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<section class="seccion-partidos-header">
        <div class="header-contenido">
            <div class="header-texto">
                <h1>Próximos Partidos</h1>
                <p>Consulta el calendario de todos nuestros equipos</p>
            </div>
            </div>
    </section>

    <section class="seccion-calendario">
        <div class="rejilla-partidos">
            
            <div class="caja-equipo full-width">
                <div class="titulo-equipo">
                    <i class="fa-solid fa-trophy"></i>
                    <div>
                        <h3>Equipo Senior</h3>
                        <p>Primera División Regional</p>
                    </div>
                </div>
                <div class="rejilla-partido-doble">
                    
                    <div class="tarjeta-partido">
                        <span class="etiqueta-proximo activo">Próximo</span>
                        <span class="jornada">Jornada 14</span>
                        <div class="enfrentamiento">
                            <div class="equipo local">
                                <img src="img/logo_bellreguard.png" alt="Bellreguard CB">
                                <p>Bellreguard CB</p>
                            </div>
                            <span class="vs">VS</span>
                            <div class="equipo visitante">
                                <img src="img/logo_gandia.png" alt="CB Gandia">
                                <p>CB Gandia</p>
                            </div>
                        </div>
                        <div class="info-adicional">
                            <div class="dato-horario"><i class="fa-regular fa-calendar-alt"></i> Sábado, 23 Noviembre 2024</div>
                            <div class="dato-horario"><i class="fa-regular fa-clock"></i> 18:00h</div>
                            <div class="dato-lugar"><i class="fa-solid fa-location-dot"></i> Pabellón Municipal Bellreguard</div>
                        </div>
                    </div>

                    <div class="tarjeta-partido">
                        <span class="etiqueta-proximo activo">Próximo</span>
                        <span class="jornada">Jornada 15</span>
                        <div class="enfrentamiento">
                            <div class="equipo local">
                                <img src="img/logo_oliva.png" alt="CB Oliva">
                                <p>CB Oliva</p>
                            </div>
                            <span class="vs">VS</span>
                            <div class="equipo visitante">
                                <img src="img/logo_bellreguard.png" alt="Bellreguard CB">
                                <p>Bellreguard CB</p>
                            </div>
                        </div>
                        <div class="info-adicional">
                            <div class="dato-horario"><i class="fa-regular fa-calendar-alt"></i> Domingo, 1 Diciembre 2024</div>
                            <div class="dato-horario"><i class="fa-regular fa-clock"></i> 11:30h</div>
                            <div class="dato-lugar"><i class="fa-solid fa-location-dot"></i> Polideportivo Municipal Oliva</div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="caja-equipo full-width">
                <div class="titulo-equipo">
                    <i class="fa-solid fa-star"></i>
                    <div>
                        <h3>Equipo Junior</h3>
                        <p>Liga Provincial Junior</p>
                    </div>
                </div>
                <div class="rejilla-partido-doble">
                    
                    <div class="tarjeta-partido">
                        <span class="etiqueta-proximo activo">Próximo</span>
                        <span class="jornada">Jornada 10</span>
                        <div class="enfrentamiento">
                            <div class="equipo local">
                                <img src="img/logo_bellreguard_jr.png" alt="Bellreguard CB Jr">
                                <p>Bellreguard CB Jr</p>
                                <span class="rol-equipo">Local</span>
                            </div>
                            <span class="vs">VS</span>
                            <div class="equipo visitante">
                                <img src="img/logo_fles_jr.png" alt="CB Fles Jr">
                                <p>CB Fles Jr</p>
                                <span class="rol-equipo">Visitante</span>
                            </div>
                        </div>
                        <div class="info-adicional">
                            <div class="dato-horario"><i class="fa-regular fa-calendar-alt"></i> Sábado, 23 Noviembre 2024</div>
                            <div class="dato-horario"><i class="fa-regular fa-clock"></i> 16:00h</div>
                            <div class="dato-lugar"><i class="fa-solid fa-location-dot"></i> Pabellón Municipal Bellreguard</div>
                        </div>
                    </div>

                    <div class="tarjeta-partido">
                        <span class="etiqueta-proximo">Próximo</span>
                        <span class="jornada">Jornada 11</span>
                        <div class="enfrentamiento">
                            <div class="equipo local">
                                <img src="img/logo_daimus_jr.png" alt="CB Daimús Jr">
                                <p>CB Daimús Jr</p>
                                <span class="rol-equipo">Local</span>
                            </div>
                            <span class="vs">VS</span>
                            <div class="equipo visitante">
                                <img src="img/logo_bellreguard_jr.png" alt="Bellreguard CB Jr">
                                <p>Bellreguard CB Jr</p>
                                <span class="rol-equipo">Visitante</span>
                            </div>
                        </div>
                        <div class="info-adicional">
                            <div class="dato-horario"><i class="fa-regular fa-calendar-alt"></i> Domingo, 1 Diciembre 2024</div>
                            <div class="dato-horario"><i class="fa-regular fa-clock"></i> 10:00h</div>
                            <div class="dato-lugar"><i class="fa-solid fa-location-dot"></i> Polideportivo Daimús</div>
                        </div>
                    </div>

                </div>
            </div>


            <div class="caja-equipo full-width">
                <div class="titulo-equipo">
                    <i class="fa-solid fa-star"></i>
                    <div>
                        <h3>Equipo Cadete</h3>
                        <p>Liga Autonómica Cadete</p>
                    </div>
                </div>
                <div class="rejilla-partido-doble">

                    <div class="tarjeta-partido">
                        <span class="etiqueta-proximo activo">Próximo</span>
                        <span class="jornada">Jornada 8</span>
                        <div class="enfrentamiento">
                            <div class="equipo local">
                                <img src="img/logo_cullera_cad.png" alt="CB Cullera Cad">
                                <p>CB Cullera Cad</p>
                            </div>
                            <span class="vs">VS</span>
                            <div class="equipo visitante">
                                <img src="img/logo_bellreguard_cad.png" alt="Bellreguard CB Cad">
                                <p>Bellreguard CB Cad</p>
                            </div>
                        </div>
                        <div class="info-adicional">
                            <div class="dato-horario"><i class="fa-regular fa-calendar-alt"></i> Domingo, 24 Noviembre 2024</div>
                            <div class="dato-horario"><i class="fa-regular fa-clock"></i> 09:30h</div>
                            <div class="dato-lugar"><i class="fa-solid fa-location-dot"></i> Pabellón Municipal Cullera</div>
                        </div>
                    </div>

                    <div class="tarjeta-partido">
                        <span class="etiqueta-proximo activo">Próximo</span>
                        <span class="jornada">Jornada 9</span>
                        <div class="enfrentamiento">
                            <div class="equipo local">
                                <img src="img/logo_bellreguard_cad.png" alt="Bellreguard CB Cad">
                                <p>Bellreguard CB Cad</p>
                            </div>
                            <span class="vs">VS</span>
                            <div class="equipo visitante">
                                <img src="img/logo_sueca_cad.png" alt="CB Sueca Cad">
                                <p>CB Sueca Cad</p>
                            </div>
                        </div>
                        <div class="info-adicional">
                            <div class="dato-horario"><i class="fa-regular fa-calendar-alt"></i> Sábado, 30 Noviembre 2024</div>
                            <div class="dato-horario"><i class="fa-regular fa-clock"></i> 15:30h</div>
                            <div class="dato-lugar"><i class="fa-solid fa-location-dot"></i> Pabellón Municipal Bellreguard</div>
                        </div>
                    </div>

                </div>
            </div>
            
        </div>
    </section>

@endsection