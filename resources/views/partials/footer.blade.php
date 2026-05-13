<footer class="pie-pagina">
    <div class="rejilla-footer">
        <div class="footer-col info-club">
            <span class="footer-kicker">Bellreguard CB</span>
            <a href="{{ url('/') }}" class="logo-footer">
                <img src="{{ asset('img/club_14.png') }}" alt="Logo Bellreguard" />
                <div class="texto-logo-footer">
                    <h3>Bellreguard</h3>
                    <span>{{ __('ui.club_name') }}</span>
                </div>
            </a>
            <p class="descripcion-breve">{{ __('ui.footer.description') }}</p>
        </div>

        <div class="footer-col">
            <h4>{{ __('ui.footer.quick_links') }}</h4>
            <ul class="lista-footer">
                <li><a href="{{ route('basket.equipo') }}">{{ __('ui.nav.team') }}</a></li>
                <li><a href="{{ route('basket.clasificacion') }}">{{ __('ui.nav.standings') }}</a></li>
                <li><a href="{{ route('basket.estadisticas') }}">{{ __('ui.nav.stats') }}</a></li>
                <li><a href="{{ route('basket.partidos') }}">{{ __('ui.nav.matches') }}</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>{{ __('ui.nav.contact') }}</h4>
            <ul class="lista-footer contacto-items">
                <li><i class="fas fa-map-marker-alt"></i> {{ __('ui.footer.municipal_pavilion') }}</li>
                <li><i class="fas fa-phone"></i> +34 620 300 902</li>
                <li><i class="fas fa-envelope"></i> info@bellreguardcb.com</li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>{{ __('ui.footer.follow_us') }}</h4>
            <p class="footer-social-text">{{ __('ui.footer.social_text') }}</p>
            <div class="redes-iconos">
                <a href="#" title="Facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/basquetbellreguard/" title="Instagram" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="YouTube" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p class="copy">{{ __('ui.footer.copyright', ['year' => date('Y')]) }}</p>
        <p class="copy footer-copy-secondary">{{ __('ui.footer.secondary_copy') }}</p>
    </div>
</footer>
