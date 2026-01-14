<footer class="pie-pagina">
    <div class="rejilla-footer">
        <div class="footer-col info-club">
            <div class="logo-footer">
                <img src="{{ asset('img/basket.jpeg') }}" alt="Logo Bellreguard" />
                <div class="texto-logo-footer">
                    <h3>Bellreguard</h3>
                    <span>Club de Basket</span>
                </div>
            </div>
            <p class="descripcion-breve">Fomentando el baloncesto y sus valores en nuestra comunidad desde hace años.</p>
        </div>

        <div class="footer-col">
            <h4>Enlaces Rápidos</h4>
            <ul class="lista-footer">
                <li><a href="{{ route('equipo') }}">Equipo</a></li>
                <li><a href="{{ route('clasificacion') }}">Clasificación</a></li>
                <li><a href="{{ route('estadisticas') }}">Estadísticas</a></li>
                <li><a href="{{ route('partidos') }}">Partidos</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Contacto</h4>
            <ul class="lista-footer contacto-items">
                <li><i class="fas fa-map-marker-alt"></i> Pabellón Municipal</li>
                <li><i class="fas fa-phone"></i> +34 620 300 902</li>
                <li><i class="fas fa-envelope"></i> info@bellreguard.com</li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Síguenos</h4>
            <div class="redes-iconos">
    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
    <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
</div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p class="copy">© {{ date('Y') }} Bellreguard Club de Basket. Todos los derechos reservados.</p>
    </div>
</footer>