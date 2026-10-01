<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * El servicio corre detras del balanceador de Render, que nos llama por
     * HTTP y anade las cabeceras X-Forwarded-*. Confiar en ellas permite:
     *  - conocer la IP real del cliente (necesario para el throttle de login),
     *  - detectar HTTPS (isSecure) para generar URLs seguras.
     *
     * @var string|string[]|bool
     */
    protected $proxies = '*';
}
