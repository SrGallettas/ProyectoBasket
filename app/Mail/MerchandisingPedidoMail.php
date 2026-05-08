<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MerchandisingPedidoMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public array $cliente,
        public array $cartItems,
        public array $cartSummary
    ) {
    }

    /*
     * ASUNTO Y REMITENTE DE RESPUESTA
     *
     * El email llega al club con un asunto claro y
     * con reply-to apuntando al correo escrito por el cliente.
     */
    public function envelope(): Envelope
    {
        $nombreCompleto = trim($this->cliente['nombre'] . ' ' . $this->cliente['apellidos']);

        return new Envelope(
            subject: 'Nueva solicitud de pedido de merchandising',
            replyTo: [
                new \Illuminate\Mail\Mailables\Address(
                    $this->cliente['correo'],
                    $nombreCompleto
                ),
            ],
        );
    }

    /*
     * CONTENIDO DEL CORREO
     *
     * Usa una vista Blade simple para renderizar
     * los datos del cliente y las líneas de la cesta.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.merchandising_pedido',
        );
    }
}
