<?php 
require_once '../global.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class EgestorService {
    private $_URI_ACCESS_TOKEN = 'https://api.egestor.com.br/v1/oauth/access_token';
    private $_API_URI = 'https://api.egestor.com.br/v1';

    private $clientApp;
    public $http;

    public function __construct($clientId, $clientSecret, $redirectUri = '') {
        $this->clientApp = [
            'id'          => $clientId,
            'secret'      => $clientSecret,
            'redirectUri' => $redirectUri
        ];
        
        $this->http = new GuzzleClient();
    } 

    private function getAccessToken() {
        try {
            $response = $this->http->post($this->_URI_ACCESS_TOKEN, [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientApp['id'],
                    'client_secret' => $this->clientApp['secret']
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function BuscarDadosEtiqueta($numeroPedido) {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return ['success' => false, 'error' => 'Falha na autenticação OAuth2 com o eGestor. Verifique as credenciais.'];
        }

        try {
            $url = $this->_API_URI . '/vendas';
            
            $response = $this->http->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
                'query' => [
                    'codigo' => $numeroPedido,
                    'expand' => 'contato,nfe'
                ]
            ]);

            $dadosVenda = json_decode($response->getBody(), true);

            if (empty($dadosVenda['data'])) {
                return ['success' => false, 'error' => "O pedido {$numeroPedido} não foi encontrado no eGestor."];
            }

            $venda = $dadosVenda['data'][0];

            $enderecoFormatado = sprintf(
                "%s, %s %s\n%s - %s / %s\nCEP: %s",
                $venda['contato']['endereco'] ?? '',
                $venda['contato']['endereco_numero'] ?? 'S/N',
                (!empty($venda['contato']['endereco_complemento'])) ? '- ' . $venda['contato']['endereco_complemento'] : '',
                $venda['contato']['bairro'] ?? '',
                $venda['contato']['cidade'] ?? '',
                $venda['contato']['uf'] ?? '',
                $venda['contato']['cep'] ?? ''
            );

            return [
                'success' => true,
                'dados' => [
                    'numero_pedido'  => $venda['codigo'] ?? $numeroPedido,
                    'nota_fiscal'    => $venda['nfe']['numero'] ?? 'Não emitida',
                    'transportadora' => $venda['transportadora']['nome'] ?? 'A COMBINAR / RETIRADA',
                    'destinatario'   => $venda['contato']['nome'] ?? 'Cliente não informado',
                    'endereco'       => $enderecoFormatado
                ]
            ];

        } catch (ClientException $e) {
            $msg = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            return ['success' => false, 'error' => 'Erro na API eGestor: ' . $msg];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erro interno do sistema: ' . $e->getMessage()];
        }
    }
}