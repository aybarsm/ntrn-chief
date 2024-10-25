<?php










namespace Symfony\Component\Mime\Crypto;

use Symfony\Component\Mime\Exception\RuntimeException;
use Symfony\Component\Mime\Message;




final class SMimeSigner extends SMime
{
private string $signCertificate;
private string|array $signPrivateKey;
private int $signOptions;
private ?string $extraCerts;








public function __construct(string $certificate, string $privateKey, ?string $privateKeyPassphrase = null, ?string $extraCerts = null, ?int $signOptions = null)
{
if (!\extension_loaded('openssl')) {
throw new \LogicException('PHP extension "openssl" is required to use SMime.');
}

$this->signCertificate = $this->normalizeFilePath($certificate);

if (null !== $privateKeyPassphrase) {
$this->signPrivateKey = [$this->normalizeFilePath($privateKey), $privateKeyPassphrase];
} else {
$this->signPrivateKey = $this->normalizeFilePath($privateKey);
}

$this->signOptions = $signOptions ?? \PKCS7_DETACHED;
$this->extraCerts = $extraCerts ? realpath($extraCerts) : null;
}

public function sign(Message $message): Message
{
$bufferFile = tmpfile();
$outputFile = tmpfile();

$this->iteratorToFile($message->getBody()->toIterable(), $bufferFile);

if (!@openssl_pkcs7_sign(stream_get_meta_data($bufferFile)['uri'], stream_get_meta_data($outputFile)['uri'], $this->signCertificate, $this->signPrivateKey, [], $this->signOptions, $this->extraCerts)) {
throw new RuntimeException(sprintf('Failed to sign S/Mime message. Error: "%s".', openssl_error_string()));
}

return new Message($message->getHeaders(), $this->convertMessageToSMimePart($outputFile, 'multipart', 'signed'));
}
}
