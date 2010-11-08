------------------------------------------------------------------------------------------------------------------

Descri��o dos arquivos
bibli.sql : estrutura da base de dados, sem dados

minimum.sql : utilizador admin/admin, par�metros da aplica��o

feed_essential.sql : o que se necessita para iniciar a aplica��o em modo in�cio r�pido :
	Dados da aplica��o preenchidos, modific�veis.
	Um conjunto de c�pia de seguran�a pronto a empregar
	Um conjunto de par�metros de Z3950.
	
data_test.sql : uma pequena selec��o de dados de registos, utilizadores, para poder experimentar o PMB.
	Registos, utilizadores, empr�stimos, exemplares, publica��es peri�dicas
	Baseia-se nos dados da aplica��o inclu�dos tamb�m em feed_essential.sql
	Deve-se carregar o thesaurus UNESCO_FR unesco_fr.sql
	
Tesauros : prop�em-se 3 thesaurus :
	unesco_fr.sql : thesaurus hier�rquico da UNESCO, bastante importante e bem constru�do.
	grumeau.sql : um poco mais pequeno, mais simples m�s tamb�m bem constru�do.
	environnement : um thesaurus �til para un fundo documental relacionado com o meio ambiente.
	
Indexations internes : prop�em-se 4 indexa��es :
	indexint_100.sql : 100 casos do saber ou margarida de cores, indexa��o decimal 
	style Dewey simplificada para educa��o
	indexint_chambery.sql : indexa��o de estilo Dewey da BM de Chamb�ry, bem concebido mas pouco adaptada
	a bibliotecas pequenas
	indexint_dewey.sql : indexa��o estilo Dewey
	indexint_small_en.sql : indexa��o estilo Dewey reduzida e em ingl�s
	

************************************************************************************************
________________________________________________________________________________________________
Aten��o, se est� a fazer uma actualiza��o de uma vers�o anterior :
------------------------------------------------------------------------------------------------
*********** A realizar a cada instala��o ou actualiza��o da aplica��o  ****************
Quando instala uma nova vers�o
sobre uma vers�o anterior deve, obrigatoriamente,
antes de copiar os arquivos novos contidos no arquivo zip
no servidor web :

comprovar que os par�metros inclu�dos em :
./includes/db_param.inc.php
./opac_css/includes/opac_db_param.inc.php

correspondem � sua configura��o (fa�a uma c�pia antes !)

Adicionalmente :
Deve fazer uma actualiza��o da base de dados.
N�o se perder� nada.

Ligue-se da forma habitual ao PMB, o est�lo gr�fico pode ser diferente, 
ausente (visualiza��o sem cores nem imagens)

V� a Administra��o > Ferramentas > act base de dados para actualizar a
base de dados.

Uma s�rie de mensagens ir�o indicando as actualiza��es sucessivas, 
para continuar a actualiza��o clique no link da parte inferior da p�gina 
at� que apare�a a mensagem 'A sua base de dados est� actualizada com a vers�o...'

Pode editar a sua conta de utilizador para modificar as suas prefer�ncias, mudando
o estilo de visualiza��o.

Fa�a chegar as suas d�vidas, problemas ou sugest�es por correio
electr�nico : pmb@sigb.net

Por outro lado, gostaremos de contar consigo como um dos nossos
utilizadores, e se nos facilitar alguns dados como o n�mero de utilizadores, de obras
de CD... junto com os dados do seu estabelecimento (ou a t�tulo particular) 
nos ajudar� a conhec�-lo melhor.

Encontrar� mais informa��o no direct�rio ./doc ou 
na nossa p�gina web http://www.sigb.net

A equipa de desenvolvimento.


///////////////////// Lista de tabelas inclu�das nos arquivos /////////////////

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            minimum.sql
# Conte�do da tabela `parametres`
# Conte�do da tabela `users`
	utilizador admin/admin

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            feed_essential.sql
# Conte�do da tabela `docs_codestat`
# Conte�do da tabela `docs_location`
# Conte�do da tabela `docs_section`
# Conte�do da tabela `docs_statut`
# Conte�do da tabela `docs_type`

# Conte�do da tabela `empr_categ`
# Conte�do da tabela `empr_codestat`

# Conte�do da tabela `lenders`

# Conte�do da tabela `sauv_lieux`
# Conte�do da tabela `sauv_sauvegardes`
# Conte�do da tabela `sauv_tables`

# Conte�do da tabela `z_attr`
# Conte�do da tabela `z_bib`


\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            data_test.sql
# Conte�do da tabela `users`
	Utilizadores suplementares
	
# Conte�do da tabela `empr`
# Conte�do da tabela `empr_custom`
# Conte�do da tabela `empr_custom_lists`
# Conte�do da tabela `empr_custom_values`
# Conte�do da tabela `empr_groupe`
# Conte�do da tabela `groupe`

# Conte�do da tabela `notices`
# Conte�do da tabela `notices_custom`
# Conte�do da tabela `notices_custom_lists`
# Conte�do da tabela `notices_custom_values`
# Conte�do da tabela `origine_notice`
# Conte�do da tabela `exemplaires`
# Conte�do da tabela `expl_custom`
# Conte�do da tabela `expl_custom_lists`
# Conte�do da tabela `expl_custom_values`
# Conte�do da tabela `explnum`

# Conte�do da tabela `authors`
# Conte�do da tabela `publishers`
# Conte�do da tabela `collections`
# Conte�do da tabela `sub_collections`
# Conte�do da tabela `responsability`
# Conte�do da tabela `series`

# Conte�do da tabela `notices_categories`

# Conte�do da tabela `bulletins`
# Conte�do da tabela `analysis`

# Conte�do da tabela `notices_categories`

# Conte�do da tabela `caddie`
# Conte�do da tabela `caddie_content`
# Conte�do da tabela `caddie_procs`

# Conte�do da tabela `etagere`
# Conte�do da tabela `etagere_caddie`


# Conte�do da tabela `resa`
# Conte�do da tabela `resa_ranger`

# Conte�do da tabela `quotas`
# Conte�do da tabela `pret`

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            agneaux.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            unesco_fr.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            environnement.sql
# Conte�do da tabela `categories`
# Conte�do da tabela `categ_assoc`

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_chambery.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_100.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_dewey.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_small_en.sql
# Conte�do da tabela `indexint`
