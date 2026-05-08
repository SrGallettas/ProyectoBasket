<footer class="pie-pagina">
    <div class="rejilla-footer">
        <div class="footer-col info-club">
            <span class="footer-kicker">Bellreguard CB</span>
            <a href="{{ url('/') }}" class="logo-footer">
                <img src="{{ asset('img/club_14.png') }}" alt="Logo Bellreguard" />
                <div class="texto-logo-footer">
                    <h3>Bellreguard</h3>
                    <span>Club de Basket</span>
                </div>
            </a>
            <p class="descripcion-breve">Fomentando el baloncesto y sus valores en nuestra comunidad desde hace años.</p>
        </div>

        <div class="footer-col">
            <h4>Enlaces Rápidos</h4>
            <ul class="lista-footer">
                <li><a href="{{ route('basket.equipo') }}">Equipo</a></li>
                <li><a href="{{ route('basket.clasificacion') }}">Clasificación</a></li>
                <li><a href="{{ route('basket.estadisticas') }}">Estadísticas</a></li>
                <li><a href="{{ route('basket.partidos') }}">Partidos</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Contacto</h4>
            <ul class="lista-footer contacto-items">
                <li><i class="fas fa-map-marker-alt"></i> Pabellón Municipal</li>
                <li><i class="fas fa-phone"></i> +34 620 300 902</li>
                <li><i class="fas fa-envelope"></i> info@bellreguardcb.com</li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Síguenos</h4>
            <p class="footer-social-text">Sigue la actualidad del club y mantente al día de partidos, noticias y ambiente del equipo.</p>
            <div class="redes-iconos">
                <a href="#" title="Facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/basquetbellreguard/" title="Instagram" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="YouTube" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p class="copy">© {{ date('Y') }} Bellreguard CB. Todos los derechos reservados.</p>
        <p class="copy footer-copy-secondary">Baloncesto, comunidad y actividad deportiva en Bellreguard.</p>
    </div>
</footer>
