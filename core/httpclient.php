<?php
class HttpClient {
    private $baseUrl;
    private $headers = [];
    private $timeout = 30;
    private $verifySSL = true;

    public function __construct(string $baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function setHeaders(array $headers): void {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function setTimeout(int $seconds): void {
        $this->timeout = $seconds;
    }

    public function setVerifySSL(bool $verify): void {
        $this->verifySSL = $verify;
    }

    public function post(string $endpoint, string $data): string {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->prepareHeaders());
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException("HTTP request failed: " . $error);
        }

        return $response;
    }

    private function prepareHeaders(): array {
        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[] = "$key: $value";
        }
        return $headers;
    }
}
