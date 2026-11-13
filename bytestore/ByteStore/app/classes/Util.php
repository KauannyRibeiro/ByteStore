<?php
// =====================================================================
// CLASSE: Util  (funções de apoio usadas em várias páginas)
// =====================================================================
// Tudo aqui é "static": chame com  Util::nome()  sem criar objeto.
// =====================================================================
final class Util
{
    // ---------------------------------------------------------------
    // SEGURANÇA: escapar texto antes de mostrar na tela
    // ---------------------------------------------------------------
    // Se alguém cadastrar um produto chamado  <script>alert(1)</script>,
    // sem escapar o navegador EXECUTARIA o script (ataque XSS).
    // Com e(), os símbolos < > " ' viram texto inofensivo.
    // REGRA: toda vez que imprimir um dado vindo do banco ou do usuário, use Util::e().
    public static function e(?string $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // Formata dinheiro: 1399.9 vira "R$ 1.399,90"
    public static function moeda(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    // ---------------------------------------------------------------
    // ENDEREÇOS (URLs)
    // ---------------------------------------------------------------
    // Monta o endereço de uma página do site:  Util::url('carrinho.php')  ->  "/carrinho.php"
    // Se o site estiver numa subpasta, o 'url_base' do config entra na frente.
    public static function url(string $caminho = ''): string
    {
        return rtrim((string) Config::get('url_base', ''), '/') . '/' . ltrim($caminho, '/');
    }

    // Endereço da página que o usuário está vendo agora (ex.: "/index.php?categoria=2").
    public static function urlAtual(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    // Campo escondido que guarda "de qual página o usuário veio".
    // Depois de adicionar ao carrinho, voltamos para essa mesma página.
    public static function campoVoltar(): string
    {
        return '<input type="hidden" name="voltar" value="' . self::e(self::urlAtual()) . '">';
    }

    // SEGURANÇA: só aceita endereços INTERNOS do nosso site.
    // Sem essa checagem, alguém poderia mandar o usuário para um site falso
    // (ataque "open redirect"). Se o endereço não for seguro, usa o $padrao.
    public static function urlSegura(string $url, string $padrao): string
    {
        $comecaComBarra = isset($url[0]) && $url[0] === '/';               // "/algo" (caminho do nosso site)
        $ehOutroSite    = strpos($url, '//') === 0 || strpos($url, '\\') !== false; // "//site.com" ou barra invertida
        $temQuebraLinha = preg_match('/[\r\n]/', $url) === 1;              // quebras de linha não podem

        if ($comecaComBarra && !$ehOutroSite && !$temQuebraLinha) {
            return $url;
        }
        return $padrao;
    }

    // Manda o navegador para outra página e ENCERRA este script (exit).
    public static function redirecionar(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    // ---------------------------------------------------------------
    // MENSAGENS "FLASH" (aparecem uma vez só)
    // ---------------------------------------------------------------
    // Depois de salvar algo, redirecionamos para outra página. A mensagem
    // "Produto salvo!" precisa sobreviver a esse redirecionamento, então
    // guardamos na SESSÃO. O layout mostra e apaga (pegarFlash).
    // $tipo = 'sucesso', 'erro' ou 'info'. O link é opcional (ex.: "Ver carrinho").
    public static function flash(string $tipo, string $mensagem, ?string $linkTexto = null, ?string $linkUrl = null): void
    {
        $_SESSION['flash'][] = [
            'tipo'       => $tipo,
            'mensagem'   => $mensagem,
            'link_texto' => $linkTexto,
            'link_url'   => $linkUrl,
        ];
    }

    // Devolve as mensagens guardadas e APAGA da sessão (cada uma aparece só uma vez).
    public static function pegarFlash(): array
    {
        $mensagens = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $mensagens;
    }
}
