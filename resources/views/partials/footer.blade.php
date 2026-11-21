<footer class="site-footer">
    <div class="site-footer__inner">
        <a class="site-brand site-brand--footer" href="{{ route('home') }}">
            <span class="site-brand__mark" aria-hidden="true">{{ strtoupper(substr(config('shop.name'), 0, 1)) }}</span>
            <span class="site-brand__text">{{ config('shop.name') }}</span>
        </a>
        <p>
            Phong cách thoải mái, cho những ngày thật là bạn.<br>
            Hotline: <a href="tel:{{ config('shop.contact.phone') }}">{{ config('shop.contact.phone') }}</a>
            · {{ config('shop.contact.address') }}
        </p>
        <div class="site-footer__bottom">
            <span>&copy; {{ date('Y') }} {{ config('shop.name') }}</span>
            <a href="{{ route('store.information') }}">Chính sách & liên hệ</a>
            <a href="{{ route('products') }}">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
        </div>
    </div>
</footer>
