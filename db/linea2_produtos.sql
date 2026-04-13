-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: linea2
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `produtos`
--

DROP TABLE IF EXISTS `produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `preco` decimal(10,2) NOT NULL DEFAULT '0.00',
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produtos`
--

LOCK TABLES `produtos` WRITE;
/*!40000 ALTER TABLE `produtos` DISABLE KEYS */;
INSERT INTO `produtos` VALUES (7,'CRUZ DECORATIVA COM ARTE RELIGIOSA EM CAMADAS DE MDF','Cruz decorativa com arte religiosa em camadas, produzida em MDF 3 mm, cortada a laser, com alto nível de detalhe e efeito tridimensional. Ideal para decoração religiosa, natalina e ambientes internos.\r\n\r\n? Dimensões aproximadas:\r\nComprimento: 30 cm\r\nLargura: 21 cm\r\nEspessura total: aproximadamente 9 mm (3 camadas)\r\n\r\nPeça composta por múltiplas camadas de MDF, criando profundidade e destaque na arte interna, com cena religiosa em estilo presépio, anjos e estrelas, proporcionando um visual elegante e diferenciado.\r\n\r\nIndicada para decoração de salas, quartos, igrejas, capelas, altares, presépios, festas de Natal e ambientes religiosos.\r\n\r\nProduzida em MDF de alta qualidade, com corte a laser de precisão, garantindo ótimo acabamento, encaixe perfeito entre as camadas e excelente durabilidade.\r\n\r\nProduto enviado na cor natural do MDF, pronto para uso ou personalização com pintura, verniz ou outros acabamentos.\r\n\r\n? Observações:\r\n• Pode haver leve variação na tonalidade do MDF\r\n• Medidas aproximadas\r\n• Produto artesanal cortado a laser\r\n• Uso recomendado em ambiente interno',53.75,NULL,'Artigo religioso',1,'2026-03-19 22:40:13','2026-03-20 17:39:23'),(8,'PRESÉPIO DECORATIVO EM MDF','Presépio decorativo produzido em MDF de 3 mm, ideal para decoração natalina. \r\n? Dimensões aproximadas: \r\nComprimento: \r\nLargura:\r\nAltura:\r\nPossui tamanho perfeito para destacar-se em mesas, prateleiras, aparadores ou altares.\r\n\r\nA peça apresenta estrutura leve e resistente, sendo excelente para personalização com pintura, aplicação de tecidos, iluminação ou outros acabamentos. Ideal para artes, lembrancinhas de Natal, decoração de ambientes ou projetos personalizados.\r\n\r\n? Observações:\r\n• Pode haver leve variação na tonalidade do MDF\r\n• Medidas aproximadas\r\n• Produto artesanal cortado a laser\r\n• Uso recomendado em ambiente interno',53.75,NULL,'Artigo religioso',1,'2026-03-19 23:02:53','2026-03-23 13:25:29'),(9,'KIT 4 UNIDADES PORTA VELAS DECORATIVO EM MDF','Conjunto com 4 porta velas decorativos confeccionados em MDF de 3 mm, perfeitos para acrescentar seu toque pessoal à decoração. \r\n? Dimensões aproximadas (cada unidade): \r\nComprimento: 10cm\r\nLargura: 10cm\r\nTamanho prático que se adapta facilmente a diversos espaços.\r\n\r\nCom bom uso em mesas, aparadores, eventos, celebrações ou uso cotidiano, também são ótimos para trabalhos artesanais. As peças permitem personalização com pintura, verniz e outras técnicas.\r\n\r\nLeves e resistentes, são uma excelente escolha tanto para uso pessoal quanto para produção de lembrancinhas.\r\n\r\n? Observações:\r\n• Pode haver leve variação na tonalidade do MDF\r\n• Medidas aproximadas\r\n• Produto artesanal cortado a laser\r\n• Uso recomendado em ambiente interno',21.00,NULL,'Decorativo',1,'2026-03-19 23:14:20','2026-03-20 18:20:28'),(10,'PORTA CANETAS (SEM NOME) EM MDF','Porta-canetas para escritório, confeccionado em MDF, com design funcional e acabamento versátil, ideal para manter sua mesa organizada no dia a dia. \r\n? Dimensões aproximadas:\r\nComprimento: 19cm\r\nLargura: 5cm\r\nAltura: 15cm\r\nOferece espaço adequado para armazenar canetas, lápis, marcadores e outros itens essenciais.\r\n\r\nAgradável em mesas de trabalho, escritórios, home office ou ambientes de estudo, sem ocupar muito espaço. A peça não possui nome personalizado, sendo perfeita para quem busca um visual neutro ou deseja customizar conforme seu estilo, utilizando pintura, adesivos ou outras técnicas artesanais.\r\n\r\n? Observações:\r\n• Pode haver leve variação na tonalidade do MDF\r\n• Medidas aproximadas\r\n• Produto artesanal cortado a laser\r\n• Uso recomendado em ambiente interno\r\n\r\nResistente e prático, é uma excelente opção para organização pessoal ou presente.',22.00,NULL,'Escritório',1,'2026-03-19 23:21:14','2026-03-23 13:25:27'),(11,'PORTA CANETAS COM SEU NOME GRAVADO EM MDF','Porta-canetas para escritório, confeccionado em MDF, com design funcional e acabamento versátil, ideal para manter sua mesa organizada no dia a dia. \r\n? Dimensões aproximadas:\r\nComprimento: 19cm\r\nLargura: 5cm\r\nAltura: 15cm \r\nOferece espaço adequado para armazenar canetas, lápis, marcadores e outros itens essenciais.\r\n\r\nAgradável em mesas de trabalho, escritórios, home office ou ambientes de estudo, sem ocupar muito espaço. A peça possui nome personalizado, sendo perfeita para quem busca demarcação profissional que siga seu estilo.\r\n\r\nResistente e prático, é uma excelente opção para organização pessoal ou presente.\r\n\r\n? Observações:\r\n• Pode haver leve variação na tonalidade do MDF\r\n• Medidas aproximadas\r\n• Produto artesanal cortado a laser\r\n• Uso recomendado em ambiente interno',38.50,NULL,'Escritório',1,'2026-03-19 23:25:06','2026-03-20 19:12:51'),(12,'CAIXA MELHOR PAI DO MUNDO EM MDF','Caixa decorativa confeccionada em MDF, com a mensagem “Melhor Pai do Mundo” gravada, para presentear seu pai de forma especial e significativa. \r\n\r\n? Dimensões aproximadas:\r\nComprimento: 14cm\r\nLargura: 16cm\r\nAltura: 4cm\r\nPossui tamanho perfeito para acomodar pequenos presentes, lembranças ou kits personalizados.\r\n\r\nÉ uma ótima opção para surpreender em datas comemorativas, podendo ser utilizada para guardar doces ou acessórios. É uma escolha encantadora para presentear e demonstrar carinho ao seu super-herói de forma única e memorável!\r\n\r\n? Observações:\r\n• Pode haver leve variação na tonalidade do MDF\r\n• Medidas aproximadas\r\n• Produto artesanal cortado a laser\r\n• Uso recomendado em ambiente interno',22.25,NULL,'Decorativo',1,'2026-03-19 23:30:08','2026-03-20 18:35:09'),(19,'CAIXA-DECORATIVA-VAZADA-COM-TAMPA','CAIXA-DECORATIVA-VAZADA-COM-TAMPA',32.00,NULL,'Modelo MDF',1,'2026-03-23 17:43:36','2026-03-23 17:43:36'),(20,'CAIXA-DE-VINHO','CAIXA-DE-VINHOCAIXA-DE-VINHOCAIXA-DE-VINHOCAIXA-DE-VINHO',56.00,NULL,'Modelo MDF',1,'2026-03-23 17:44:12','2026-03-23 17:44:12'),(21,'RODA-GIGANTE-PORTA-RETRATOS','RODA-GIGANTE-PORTA-RETRATOSRODA-GIGANTE-PORTA-RETRATOSRODA-GIGANTE-PORTA-RETRATOS',324.00,NULL,'Modelo MDF',1,'2026-03-23 17:44:57','2026-03-23 17:44:57'),(22,'SUPORTE-CELULAR-DOG','SUPORTE-CELULAR-DOGSUPORTE-CELULAR-DOGSUPORTE-CELULAR-DOGSUPORTE-CELULAR-DOG',34.00,NULL,'Modelo MDF',1,'2026-03-23 17:45:32','2026-03-23 17:45:32'),(23,'LUMINARIA-DE-PAREDE-DOBBY','LUMINARIA-DE-PAREDE-DOBBYLUMINARIA-DE-PAREDE-DOBBYLUMINARIA-DE-PAREDE-DOBBYLUMINARIA-DE-PAREDE-DOBBY',33.00,NULL,'Modelo MDF',1,'2026-03-23 17:46:07','2026-03-23 17:46:07');
/*!40000 ALTER TABLE `produtos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-12 20:24:02
