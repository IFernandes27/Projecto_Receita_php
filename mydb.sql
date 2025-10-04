-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 04-Out-2025 às 19:37
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mydb`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categoria`
--

CREATE TABLE `categoria` (
  `idcategoria` int(11) NOT NULL,
  `nome_categoria` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `categoria`
--

INSERT INTO `categoria` (`idcategoria`, `nome_categoria`) VALUES
(1, 'Doces'),
(2, 'Mariscos'),
(3, 'Peixes'),
(4, 'Massa'),
(6, 'Salada'),
(7, 'Carnes'),
(8, 'Sem categoria'),
(9, 'Bebidas');

-- --------------------------------------------------------

--
-- Estrutura da tabela `encomendas`
--

CREATE TABLE `encomendas` (
  `idencomendas` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `utilizador_id_utilizador` int(11) NOT NULL,
  `Pagamento_idPagamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `encomendas`
--

INSERT INTO `encomendas` (`idencomendas`, `data`, `valor_total`, `utilizador_id_utilizador`, `Pagamento_idPagamento`) VALUES
(4, '2025-07-29 17:03:58', 12.00, 6, 1),
(5, '2025-07-29 17:03:58', 7.00, 12, 2),
(6, '2025-08-21 16:35:57', 6.98, 13, 1),
(7, '2025-08-21 16:43:57', 4.29, 13, 1),
(8, '2025-08-22 18:38:48', 8.28, 13, 1),
(9, '2025-08-22 18:39:36', 2.99, 13, 1),
(10, '2025-08-22 20:44:28', 7.28, 13, 1),
(11, '2025-08-22 20:52:23', 2.99, 13, 1),
(12, '2025-08-25 17:24:34', 4.29, 13, 1),
(13, '2025-08-25 20:08:49', 7.28, 13, 1),
(14, '2025-08-27 19:49:31', 2.99, 13, 1),
(15, '2025-09-14 23:54:49', 2.50, 13, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `encomenda_receita`
--

CREATE TABLE `encomenda_receita` (
  `receita_idreceita` int(11) NOT NULL,
  `encomendas_idencomendas` int(11) NOT NULL,
  `Observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `encomenda_receita`
--

INSERT INTO `encomenda_receita` (`receita_idreceita`, `encomendas_idencomendas`, `Observacoes`) VALUES
(2, 4, NULL),
(2, 6, NULL),
(3, 10, NULL),
(4, 4, NULL),
(5, 6, NULL),
(7, 12, NULL),
(9, 5, NULL),
(9, 8, NULL),
(10, 5, NULL),
(11, 9, NULL),
(12, 10, NULL),
(12, 11, NULL),
(12, 14, NULL),
(13, 7, NULL),
(13, 8, NULL),
(13, 13, NULL),
(15, 13, NULL),
(22, 15, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamento`
--

CREATE TABLE `pagamento` (
  `idPagamento` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `pagamento`
--

INSERT INTO `pagamento` (`idPagamento`, `nome`) VALUES
(1, 'multibanco'),
(2, 'visa'),
(3, 'mbaway'),
(4, 'paypal');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pais`
--

CREATE TABLE `pais` (
  `idpais` int(11) NOT NULL,
  `Pais` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `pais`
--

INSERT INTO `pais` (`idpais`, `Pais`) VALUES
(1, 'Brasil'),
(2, 'Portugal'),
(3, 'Angola'),
(4, 'Moçambique'),
(5, 'Cabo Verde'),
(6, 'São Tome'),
(7, 'Guiné Bissau'),
(8, 'Guiné Equatorial'),
(9, 'Timor Leste');

-- --------------------------------------------------------

--
-- Estrutura da tabela `receita`
--

CREATE TABLE `receita` (
  `idreceita` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `preco` float NOT NULL,
  `imagens` varchar(200) NOT NULL,
  `descricao` varchar(250) NOT NULL,
  `ingredientes` longtext NOT NULL,
  `preparacao` longtext NOT NULL,
  `pais_idpais` int(11) NOT NULL,
  `categoria_idcategoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `receita`
--

INSERT INTO `receita` (`idreceita`, `nome`, `preco`, `imagens`, `descricao`, `ingredientes`, `preparacao`, `pais_idpais`, `categoria_idcategoria`) VALUES
(1, 'Bacalhau à Brás', 3.99, 'bacalhau-bras-1.JPG', 'Prato típico de Lisboa, feito com bacalhau desfiado, batata frita palha, ovos mexidos e cebola. É simples, reconfortante e muito apreciado em todo o país.', '400g de bacalhau demolhado e desfiado\r\n\r\n300g de batata palha\r\n\r\n1 cebola grande (às rodelas finas)\r\n\r\n4 ovos\r\n\r\n2 dentes de alho picados\r\n\r\nAzeite q.b.\r\n\r\nSalsa picada q.b.\r\n\r\nAzeitonas pretas q.b.\r\n\r\nSal e pimenta q.b.', 'Refogue a cebola e o alho em azeite até ficarem dourados.\r\n\r\nJunte o bacalhau desfiado e deixe cozinhar por 5-7 minutos.\r\n\r\nAdicione a batata palha e envolva bem.\r\n\r\nBata os ovos com um pouco de sal e pimenta, junte ao tacho e mexa até os ovos estarem cozidos mas cremosos.\r\n\r\nPolvilhe com salsa e sirva com azeitonas pretas.', 2, 3),
(2, 'Pastel de Nata', 2.99, 'pastel-natas.JPG', 'Os famosos pastéis de nata são tartes de massa folhada com recheio cremoso de nata e gema de ovo, tipicamente servidos polvilhados com canela. Uma das maiores iguarias da doçaria portuguesa.', '1 rolo de massa folhada\r\n\r\n250 ml de natas\r\n\r\n250 ml de leite\r\n\r\n150 g de açúcar\r\n\r\n6 gemas de ovo\r\n\r\n1 colher de sopa de farinha\r\n\r\n1 casca de limão\r\n\r\n1 pau de canela', 'Forre formas de queque com massa folhada, pressionando bem.\r\n\r\nMisture a farinha com um pouco de leite para desfazer. Junte o restante leite, as natas, o açúcar, a casca de limão e o pau de canela.\r\n\r\nLeve ao lume e mexa até engrossar ligeiramente. Retire do lume, deixe arrefecer um pouco e junte as gemas batidas.\r\n\r\nRetire a casca de limão e o pau de canela, verta o creme nas formas.\r\n\r\nLeve ao forno pré-aquecido a 220 °C por 15–20 minutos até estarem dourados.\r\n\r\nSirva polvilhados com canela e/ou açúcar em pó.', 2, 1),
(3, 'Feijoada', 4.29, 'feijoada.JPG', 'A feijoada é um prato típico brasileiro, especialmente consumido às quartas-feiras e aos sábados. É feita com feijão preto, carnes de porco e acompanhamentos como arroz, farofa, couve e laranja.', '500 g de feijão preto\r\n\r\n300 g de carne seca\r\n\r\n300 g de costela de porco salgada\r\n\r\n200 g de linguiça calabresa\r\n\r\n200 g de paio\r\n\r\n1 cebola picada\r\n\r\n4 dentes de alho picados\r\n\r\n2 folhas de louro\r\n\r\nSal e pimenta a gosto\r\n\r\nÁgua q.b.\r\n\r\nÓleo ou azeite para refogar', 'Deixe as carnes salgadas de molho em água por 12 horas, trocando a água algumas vezes.\r\n\r\nCozinhe o feijão em água com as folhas de louro até ficar macio. Reserve.\r\n\r\nCozinhe separadamente as carnes até estarem macias.\r\n\r\nRefogue o alho e a cebola numa panela grande com um pouco de óleo.\r\n\r\nJunte as carnes e o feijão cozido (com parte do caldo) e deixe apurar por 30–40 minutos.\r\n\r\nSirva com arroz branco, farofa, couve refogada e rodelas de laranja.', 1, 7),
(4, 'Bolo de Fubá Cremoso', 0, 'bolo_fuba.JPG', 'Um bolo típico das festas juninas e do café da tarde brasileiro. Feito com fubá (farinha de milho fina), este bolo tem uma textura cremosa no meio, quase como um pudim.', '4 ovos\r\n\r\n2 colheres de sopa de manteiga\r\n\r\n2 xícaras de açúcar\r\n\r\n4 xícaras de leite\r\n\r\n1 xícara de farinha de trigo\r\n\r\n1 xícara de fubá\r\n\r\n1 colher de sopa de fermento em pó\r\n\r\n50 g de queijo parmesão ralado (opcional)', 'Bata todos os ingredientes no liquidificador até obter uma mistura homogénea.\r\n\r\nDespeje numa forma untada e enfarinhada.\r\n\r\nLeve ao forno pré-aquecido a 180 °C por cerca de 45–50 minutos, ou até dourar.\r\n\r\nDeixe arrefecer antes de desenformar.\r\n\r\nO bolo forma naturalmente uma camada cremosa no meio.', 1, 1),
(5, 'Muamba de Galinha', 3.99, 'moamba_galinha.JPG', 'A Muamba de Galinha é um dos pratos mais emblemáticos de Angola. Trata-se de frango cozinhado com óleo de palma (dendem), quiabos e outros temperos, servido normalmente com funge (massa de farinha de mandioca ou milho).', '1 frango inteiro cortado em pedaços\r\n\r\n1 cebola grande picada\r\n\r\n2 tomates maduros picados\r\n\r\n2 dentes de alho picados\r\n\r\n250 ml de óleo de palma (azeite de dendém)\r\n\r\n10 quiabos\r\n\r\n1 folha de louro\r\n\r\nSal e piri-piri (ou malagueta) a gosto\r\n\r\nÁgua q.b.', 'Tempere o frango com sal, alho e piri-piri.\r\n\r\nRefogue a cebola e o tomate no óleo de palma até amolecerem.\r\n\r\nJunte o frango e a folha de louro, refogue por alguns minutos.\r\n\r\nAcrescente um pouco de água, tape a panela e cozinhe em lume brando por 30–40 minutos.\r\n\r\nAdicione os quiabos cortados e deixe cozinhar por mais 10 minutos.\r\n\r\nSirva com funge de mandioca ou de milho.', 3, 7),
(6, 'Funge de Mandioca', 2.99, 'funji.JPG', 'O funge é um acompanhamento essencial na culinária angolana, feito com farinha de mandioca (ou milho). Tem uma consistência elástica e é ideal para acompanhar molhos, carnes e caldos.', '2 chávenas de farinha de mandioca (ou milho)\r\n\r\n1 litro de água\r\n\r\nSal a gosto (opcional)', 'Leve a água ao lume até começar a ferver.\r\n\r\nCom a água bem quente, vá juntando a farinha aos poucos, mexendo vigorosamente com uma colher de pau.\r\n\r\nContinue a mexer até obter uma massa espessa e homogénea, sem grumos.\r\n\r\nCozinhe por mais alguns minutos, mexendo sempre até ficar com consistência de puré elástico.\r\n\r\nSirva quente, moldado em bolas, como acompanhamento.', 3, 4),
(7, 'Cachupa Tradicional', 4.29, 'cachupa.JPG', 'A Cachupa é o prato nacional de Cabo Verde. Trata-se de um guisado espesso à base de milho, feijão, carne ou peixe, e legumes. A versão \"rica\" leva várias carnes (linguiça, porco, frango), enquanto a versão \"pobre\" é mais simples, sem carne.', '500 g de milho seco\r\n\r\n250 g de feijão (preto, pedra ou catarino)\r\n\r\n200 g de carne de porco (costela ou entremeada)\r\n\r\n1 chouriço ou linguiça\r\n\r\n2 cenouras\r\n\r\n1 couve (ou repolho)\r\n\r\n1 cebola picada\r\n\r\n2 dentes de alho\r\n\r\n2 folhas de louro\r\n\r\nSal e pimenta q.b.\r\n\r\nAzeite q.b.\r\n\r\nÁgua q.b.', 'Demolhe o milho e o feijão em água por 12 horas.\r\n\r\nCozinhe o milho e o feijão numa panela grande com água, louro e um pouco de sal até ficarem quase macios.\r\n\r\nNoutra panela, refogue a cebola, o alho e as carnes em azeite.\r\n\r\nJunte os legumes cortados e deixe cozinhar uns minutos.\r\n\r\nAcrescente a mistura ao milho e feijão, cubra com água e deixe apurar em lume brando até tudo estar bem cozido.\r\n\r\nSirva quente, idealmente no dia seguinte, quando os sabores estão mais apurados.', 5, 7),
(8, ' Atum Grelhado com Molho Cru', 2.99, 'atum-grelhado.JPG', 'Este prato é muito popular nas ilhas, especialmente em São Vicente e Santo Antão. O atum fresco é grelhado e servido com um molho cru feito à base de cebola, alho e azeite. Simples, saboroso e muito típico.', '4 postas de atum fresco\r\n\r\n1 cebola grande\r\n\r\n2 dentes de alho\r\n\r\n100 ml de azeite\r\n\r\nSumo de 1 limão\r\n\r\nSal e pimenta q.b.\r\n\r\nPiri-piri (opcional)', 'Tempere o atum com sal, pimenta e sumo de limão.\r\n\r\nGrelhe as postas numa grelha bem quente, cerca de 3–4 minutos de cada lado.\r\n\r\nPara o molho cru: pique a cebola e o alho bem fininhos, misture com o azeite e, se quiser, umas gotas de piri-piri.\r\n\r\nSirva o atum grelhado com o molho cru por cima. Acompanha bem com arroz branco, mandioca cozida ou batata-doce.', 5, 7),
(9, 'Camarão à Moçambicana', 3.99, 'camarao-mocamb.JPG', 'Um prato típico da costa moçambicana, onde os camarões são cozinhados com leite de coco, alho, cebola, limão e piri-piri, formando um molho cremoso e picante. É servido geralmente com arroz branco.', '800 g de camarão médio ou grande, limpo\r\n\r\n1 cebola grande picada\r\n\r\n3 dentes de alho picados\r\n\r\n1 colher de chá de piri-piri (ou malagueta)\r\n\r\n1 colher de chá de gengibre ralado\r\n\r\nSumo de 1 limão\r\n\r\n250 ml de leite de coco\r\n\r\n2 colheres de sopa de óleo ou azeite\r\n\r\nSal q.b.\r\n\r\nCoentros frescos (opcional)', 'Tempere os camarões com sal, limão e piri-piri.\r\n\r\nRefogue a cebola, alho e gengibre no óleo até dourar.\r\n\r\nJunte os camarões e salteie por 2–3 minutos.\r\n\r\nAdicione o leite de coco e deixe cozinhar por mais 5–7 minutos até o molho engrossar ligeiramente.\r\n\r\nFinalize com coentros picados (se desejar) e sirva com arroz branco.', 4, 7),
(10, 'Matapa', 0, 'matapa.JPG', 'A Matapa é um dos pratos mais tradicionais de Moçambique. É feita com folhas de mandioca esmagadas, cozidas com amendoim moído e leite de coco. Pode levar camarões secos e é servida com arroz ou xima (papa de milho).', '500 g de folhas de mandioca esmagadas (ou espinafres como alternativa)\r\n\r\n150 g de amendoim moído\r\n\r\n200 ml de leite de coco\r\n\r\n1 cebola picada\r\n\r\n2 dentes de alho picados\r\n\r\n2 colheres de sopa de óleo de amendoim ou vegetal\r\n\r\nCamarões secos (opcional)\r\n\r\nSal q.b.', 'Lave bem as folhas de mandioca esmagadas.\r\n\r\nRefogue a cebola e o alho no óleo.\r\n\r\nAdicione as folhas, os amendoins e um pouco de água. Cozinhe por 30 minutos em lume brando.\r\n\r\nJunte o leite de coco e, se quiser, os camarões secos. Cozinhe mais 10–15 minutos até engrossar.\r\n\r\nSirva com arroz ou xima (massa de farinha de milho semelhante ao funge).\r\n\r\n', 4, 6),
(11, 'Calulu de Peixe', 2.99, 'calulu.JPG', 'O Calulu é um prato típico de São Tomé e Príncipe (também comum em Angola), feito com peixe seco, legumes e folhas verdes (como folhas de mandioca ou espinafres), cozinhados lentamente com azeite de dendém (óleo de palma). É geralmente servido com ar', '300 g de peixe seco (ex: corvina ou garoupa)\r\n\r\n300 g de espinafres ou folhas de mandioca\r\n\r\n1 beringela\r\n\r\n2 tomates maduros\r\n\r\n1 cebola grande\r\n\r\n2 dentes de alho\r\n\r\n100 ml de azeite de dendém (óleo de palma)\r\n\r\nSal e piri-piri q.b.\r\n\r\nÁgua q.b.', 'Demolhe o peixe seco em água durante algumas horas, depois limpe e corte em pedaços.\r\n\r\nNum tacho, refogue a cebola, o alho e os tomates picados no azeite de dendém.\r\n\r\nJunte a beringela cortada em rodelas, as folhas verdes e o peixe.\r\n\r\nAdicione um pouco de água e deixe cozinhar lentamente, em lume brando, por 40–50 minutos.\r\n\r\n', 6, 7),
(12, 'Banana Pão Frita com Molho de Feijão', 2.99, 'banana-pao.JPG', 'Um acompanhamento ou prato leve muito apreciado em São Tomé. As bananas pão (semelhantes à banana-da-terra) são fritas e servidas com um molho de feijão encorpado, geralmente temperado com cebola, alho e piri-piri.', '4 bananas pão maduras, cortadas em rodelas grossas\r\n\r\nÓleo para fritar\r\n\r\n300 g de feijão catarino ou feijão-preto cozido\r\n\r\n1 cebola picada\r\n\r\n2 dentes de alho picados\r\n\r\n1 tomate picado\r\n\r\nPiri-piri e sal q.b.\r\n\r\nAzeite q.b.', 'Frite as bananas pão em óleo bem quente até dourarem. Escorra em papel absorvente.\r\n\r\nNum tacho, refogue o alho, a cebola e o tomate em azeite.\r\n\r\nAcrescente o feijão já cozido com um pouco do caldo.\r\n\r\nDeixe engrossar e tempere com sal e piri-piri.\r\n\r\nSirva o molho quente sobre as bananas fritas ou à parte como acompanhamento.', 6, 6),
(13, 'Jollof Rice ', 4.29, 'jollof-rice.JPG', 'Embora originário da África Ocidental, o Jollof Rice é muito consumido na Guiné-Bissau. É um arroz aromático e colorido, cozido com tomate, cebola, especiarias e, frequentemente, frango ou peixe. Cada país tem sua própria variação.', '2 chávenas de arroz\r\n\r\n4 coxas de frango (ou peixe)\r\n\r\n1 cebola grande picada\r\n\r\n2 dentes de alho\r\n\r\n3 tomates maduros (ou 200 ml de polpa de tomate)\r\n\r\n1 pimento vermelho\r\n\r\n1 cenoura (opcional)\r\n\r\n2 folhas de louro\r\n\r\n1 colher de chá de piri-piri ou malagueta\r\n\r\nCaldo de galinha ou água q.b.\r\n\r\nÓleo de palma ou vegetal q.b.\r\n\r\nSal e pimenta q.b.', 'Tempere e cozinhe o frango com alho, sal e louro até ficar dourado. Reserve.\r\n\r\nNum tacho, refogue a cebola, o pimento, os tomates picados e o piri-piri no óleo.\r\n\r\nAcrescente o arroz e mexa para envolver no refogado.\r\n\r\nAdicione o frango e caldo suficiente para cozer o arroz.\r\n\r\nTape e cozinhe em lume médio-baixo até o arroz absorver todo o líquido.\r\n\r\nSirva quente, decorado com legumes cozidos ou salada.', 7, 7),
(14, 'Caldeirada de Peixe à Guineense', 0, 'calderada-peixe.JPG', 'A caldeirada de peixe é muito comum nas zonas costeiras da Guiné-Bissau. É feita com peixe fresco, legumes e especiarias locais, cozinhados num molho leve e saboroso, muitas vezes com óleo de palma.', '800 g de peixe fresco (garoupa, robalo, tilápia) cortado em postas\r\n\r\n1 cebola grande às rodelas\r\n\r\n2 tomates picados\r\n\r\n1 pimento verde ou vermelho\r\n\r\n2 batatas doces (ou inhame)\r\n\r\n100 ml de óleo de palma (ou vegetal)\r\n\r\n2 dentes de alho\r\n\r\n1 folha de louro\r\n\r\nSumo de 1 limão\r\n\r\nSal e malagueta q.b.\r\n\r\nÁgua q.b.', 'Tempere o peixe com sal, limão e alho. Deixe repousar 15 minutos.\r\n\r\nNum tacho, faça camadas de cebola, tomate, pimento e batata doce cortada.\r\n\r\nColoque o peixe por cima e regue com o óleo de palma.\r\n\r\nAcrescente o louro, malagueta e um pouco de água.\r\n\r\nTape e cozinhe em lume médio por cerca de 30–40 minutos, sem mexer para o peixe não se desfazer.\r\n\r\nSirva com arroz branco ou funge.', 7, 7),
(15, 'Sopa de Amendoim', 2.99, 'sopa-Amendoim.JPG', 'A sopa de amendoim é um prato tradicional em toda a África Central, incluindo a Guiné Equatorial. É rica, cremosa e ligeiramente picante, feita com pasta de amendoim, tomate e, normalmente, carne ou peixe.\r\n\r\n', '500 g de frango, carne ou peixe\r\n\r\n200 g de pasta de amendoim natural\r\n\r\n2 tomates maduros\r\n\r\n1 cebola\r\n\r\n2 dentes de alho\r\n\r\n1 litro de caldo de carne ou água\r\n\r\nMalagueta ou piri-piri a gosto\r\n\r\nSal e óleo vegetal q.b.\r\n\r\nEspinafres ou folhas de mandioca (opcional)', 'Refogue a cebola e o alho picados até dourarem.\r\n\r\nAdicione o tomate picado e deixe cozinhar até formar um molho.\r\n\r\nAcrescente a carne ou peixe e refogue ligeiramente.\r\n\r\nMisture a pasta de amendoim e vá juntando o caldo aos poucos, mexendo bem.\r\n\r\nDeixe cozinhar por cerca de 30 minutos até o molho engrossar.\r\n\r\nSe desejar, adicione folhas verdes nos últimos minutos.\r\n\r\nSirva com arroz branco, banana-pão ou mandioca cozida.', 8, 6),
(16, 'Banana-pão Cozida com Molho Picante', 0, 'banana-pao-cozido.JPG', 'A banana-pão (banana-da-terra) é um alimento básico na Guiné Equatorial. É frequentemente cozida ou frita e servida com molhos apimentados, carne ou peixe. Este acompanhamento é simples, nutritivo e t', '1 cebola picada\r\n\r\n2 tomates picados\r\n\r\n1 malagueta ou piri-piri\r\n\r\n1 dente de alho\r\n\r\nÓleo de palma ou vegetal q.b.\r\n\r\nSal a gosto', 'Descasque e corte as bananas em pedaços grandes. Cozinhe em água com sal até ficarem macias. Reserve.\r\n\r\nPara o molho, refogue a cebola, alho, tomate e piri-piri no óleo até formar um molho espesso.\r\n\r\nSirva a banana cozida regada com o molho ou à parte como acompanhamento de carne ou peixe.', 8, 6),
(17, 'Ikan Sabuko', 0, 'ikan-sabuco.JPG', 'O Ikan Sabuko é um prato tradicional timorense feito com cavala (ou outro peixe azul) marinado em tamarindo e grelhado, acompanhado com legumes cozidos ou arroz. É simples, saudável e muito popular em zonas costeiras.', '4 cavalas limpas (ou outro peixe fresco)\r\n\r\n2 colheres de sopa de polpa de tamarindo\r\n\r\n3 dentes de alho picados\r\n\r\n1 cebola pequena picada\r\n\r\n1 malagueta (opcional)\r\n\r\nSal e pimenta q.b.\r\n\r\nSumo de 1 limão\r\n\r\nÓleo q.b.', 'Misture a polpa de tamarindo com alho, cebola, limão, sal, pimenta e malagueta.\r\n\r\nMarine o peixe nesta mistura por pelo menos 30 minutos.\r\n\r\nGrelhe o peixe em grelha ou frigideira com um fio de óleo, até dourar de ambos os lados.\r\n\r\nSirva com arroz branco, legumes cozidos ou salada de pepino e tomate.', 9, 7),
(18, 'Batar Da’an', 0, 'batar.JPG', 'O Batar Da’an é um prato vegetariano tradicional de Timor-Leste feito com milho, feijão e abóbora. É nutritivo, simples e muito consumido como acompanhamento ou prato principal.', '2 chávenas de grãos de milho cozidos\r\n\r\n1 chávena de feijão verde ou feijão-frade cozido\r\n\r\n2 chávenas de abóbora cortada em cubos\r\n\r\n1 cebola picada\r\n\r\n2 dentes de alho picados\r\n\r\nSal e azeite q.b.', 'Cozinhe o milho e o feijão separadamente até estarem macios.\r\n\r\nNuma panela, refogue a cebola e o alho em azeite.\r\n\r\nJunte a abóbora e cozinhe até começar a amolecer.\r\n\r\nAcrescente o milho e o feijão, mexa bem e deixe cozinhar em lume brando até os sabores se unirem.\r\n\r\nAjuste o sal e sirva quente, como acompanhamento de carne ou peixe, ou como prato vegetariano principal.', 9, 6),
(21, 'Caipirinha', 3.99, 'rec_20250914_233812_91aca77d.jpg', 'Considerada a bebida nacional do Brasil, feita com cachaça, limão, açúcar e gelo.', '1 limão em pedaços\r\n\r\n2 colheres de sopa de açúcar\r\n\r\n50 ml de cachaça\r\n\r\nGelo a gosto', 'Macere o limão com o açúcar, adicione a cachaça e o gelo, e misture bem.', 1, 9),
(22, 'Poncha', 2.5, 'rec_20250914_235017_2e11863a.jpg', 'Bebida tradicional da Ilha da Madeira, feita com aguardente de cana, mel e limão.', '50 ml de aguardente de cana\r\n\r\n2 colheres de sopa de mel de abelha\r\n\r\nSuco de 1 limão\r\n\r\nGelo opcional', 'Num copo alto, coloque a casca do limão.\r\nAcrescente o açúcar mascavado e esmague com um pilão.\r\nEsprema o sumo dos limões e das laranjas e adicione à mistura de casca e açúcar. Misture com o mexelhote.\r\nCoe a mistura para o jarro. Adicione a aguardente de cana. Junte o mel e misture. Sirva simples ou com gelo.\r\nPara uma poncha com fruta, adicione a fruta desejada. Neste caso, adicionou-se a polpa de quatro maracujás.', 2, 9);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo_utilizador`
--

CREATE TABLE `tipo_utilizador` (
  `id_tipo` int(11) NOT NULL,
  `tipo` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `tipo_utilizador`
--

INSERT INTO `tipo_utilizador` (`id_tipo`, `tipo`) VALUES
(1, 'adm'),
(2, 'cliente');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador`
--

CREATE TABLE `utilizador` (
  `id_utilizador` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `username` varchar(45) NOT NULL,
  `email` varchar(65) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_tipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `utilizador`
--

INSERT INTO `utilizador` (`id_utilizador`, `nome`, `username`, `email`, `telefone`, `password`, `id_tipo`) VALUES
(2, 'João Pereira', 'joao.pereira', 'joao.pereira@example.com', '99876-5432', 'J0P#2025br', 2),
(6, 'Ricardoy Almeida', 'ricardo.almeiday', 'ricardo.almeida@example.com', '+351912345678', 'R1c@2025pt', 1),
(9, 'Carla Neves', 'carla.neves', 'carla.neves@example.com', '+244923987654', 'C@rl4Ang!', 2),
(12, 'Miguel Lopes', 'miguel.lopes', 'miguel.lopes@example.com', '+245955123456', 'M!gu3lGB25', 2),
(13, 'Obito Uchira', 'Tobi', 'uchiraobito@gmail.com', '9649573456', '$2y$10$Nu7zjgnYZDj4qUlLac0bGOZyqQ0F7qkay7kUEOtugyRFIyh.HBQoK', 2),
(14, 'Thanos', 'thanos27', 'thanos@gmail.com', '964957371', '$2y$10$5XVp/phTRN5rKxid9iWeDeb4b1opnB1RRnwIjTrucd8qNRRPXsuH2', 1),
(15, 'Cassandra Afonso', 'cassandra21', 'cassafonfo@gmail.com', '964957371', '$2y$10$HNuIJxJxi8nWuy1t7XmSr.N7sCpk/koZrRinyGuhc1EPjLK8CipZm', 2);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Índices para tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD PRIMARY KEY (`idencomendas`),
  ADD KEY `fk_encomendas_utilizador1_idx` (`utilizador_id_utilizador`),
  ADD KEY `fk_encomendas_Pagamento1_idx` (`Pagamento_idPagamento`);

--
-- Índices para tabela `encomenda_receita`
--
ALTER TABLE `encomenda_receita`
  ADD PRIMARY KEY (`receita_idreceita`,`encomendas_idencomendas`),
  ADD KEY `fk_receita_has_encomendas_encomendas1_idx` (`encomendas_idencomendas`),
  ADD KEY `fk_receita_has_encomendas_receita1_idx` (`receita_idreceita`);

--
-- Índices para tabela `pagamento`
--
ALTER TABLE `pagamento`
  ADD PRIMARY KEY (`idPagamento`);

--
-- Índices para tabela `pais`
--
ALTER TABLE `pais`
  ADD PRIMARY KEY (`idpais`);

--
-- Índices para tabela `receita`
--
ALTER TABLE `receita`
  ADD PRIMARY KEY (`idreceita`),
  ADD KEY `fk_receita_pais_idx` (`pais_idpais`),
  ADD KEY `fk_receita_categoria1_idx` (`categoria_idcategoria`);

--
-- Índices para tabela `tipo_utilizador`
--
ALTER TABLE `tipo_utilizador`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Índices para tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id_utilizador`),
  ADD KEY `fk_utilizador_tipo_utilizador1_idx` (`id_tipo`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `encomendas`
--
ALTER TABLE `encomendas`
  MODIFY `idencomendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `pagamento`
--
ALTER TABLE `pagamento`
  MODIFY `idPagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `pais`
--
ALTER TABLE `pais`
  MODIFY `idpais` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `receita`
--
ALTER TABLE `receita`
  MODIFY `idreceita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id_utilizador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD CONSTRAINT `fk_encomendas_Pagamento1` FOREIGN KEY (`Pagamento_idPagamento`) REFERENCES `pagamento` (`idPagamento`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_encomendas_utilizador1` FOREIGN KEY (`utilizador_id_utilizador`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `encomenda_receita`
--
ALTER TABLE `encomenda_receita`
  ADD CONSTRAINT `fk_receita_has_encomendas_encomendas1` FOREIGN KEY (`encomendas_idencomendas`) REFERENCES `encomendas` (`idencomendas`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_receita_has_encomendas_receita1` FOREIGN KEY (`receita_idreceita`) REFERENCES `receita` (`idreceita`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `receita`
--
ALTER TABLE `receita`
  ADD CONSTRAINT `fk_receita_categoria1` FOREIGN KEY (`categoria_idcategoria`) REFERENCES `categoria` (`idcategoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_receita_pais` FOREIGN KEY (`pais_idpais`) REFERENCES `pais` (`idpais`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `fk_utilizador_tipo_utilizador1` FOREIGN KEY (`id_tipo`) REFERENCES `tipo_utilizador` (`id_tipo`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
