<footer class="site-footer">
    <div class="site-footer__inner">
        <a class="site-brand site-brand--footer" href="{{ route('home') }}">
            <span class="site-brand__mark" aria-hidden="true">S</span>
            <span class="site-brand__text">shop<span>laravel</span></span>
        </a>
        <p>Phong cách thoải mái, cho những ngày thật là bạn.</p>
        <div class="site-footer__bottom">
            <span>&copy; {{ date('Y') }} Shop Laravel</span>
            <a href="{{ route('products') }}">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
        </div>
    </div>
</footer>
