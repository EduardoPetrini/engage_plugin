# Plugin Eduardo Petrini - Trabalho Final 
Curso introdutório de desenvolvimento de plugins para a plataforma Moodle. 

Plugin desenvolvido como exigência do trabalho prático final e para conclusão do curso.

# Plugin de Engajamento
O plugin desenvolvido busca mensurar o engajamento dos usuários em uma sala de aula. O engajamento foi obtido por meio da proporção de logs gerados por cada usuário.
O critério utilizado foi: quanto mais logs um usuário gera em um curso, mais engajado está. 

# Descrição do Plugin
## Tela Principal
Ao acessar a tela principal do plugin é apresentado uma tabela, com a lista de todos os courses do Moodle. Nessa tabela é exibido o nome do course, a data de último acesso de algum usuário no course, a quantidade de usuários e a quantidade de logs que foram gerados no course pelos usuários até então.

Os nomes dos courses são links para a próxima página do plugin. 
O link é gerado somente se o course possuir algum usuário devidamente matriculado a ele.

Mais abaixo há uma representação gráfica em barra dos courses e da quantidade de logs gerados por cada um.

## Segunda tela
Ao clicar no nome de algum course que possua link, o usuário é levado para a segunda tela do plugin. Nessa nova tela são exibidos uma tabela e dois gráficos referentes aos usuários do course em questão.

Na tabela é apresentado o nome do usuário, a data de último acesso no course, a nota no course até o momento e o engajamento, apresentado na última coluna como porcentagem.

O nome do usuário é um link para que seja possível enviar uma mensagem ao respectivo usuário. Esse recurso foi pensado para que o professor ou gestor que esteja analisando os engajamentos dos usuários possa enviar uma mensagem motivacional para aqueles que apresentem um engajamento baixo.

Abaixo há um gráfico de pizza destacando a proporção de engajamento entre os usuários. E mais abaixo há o gŕafico de barras representando o engajamento de cada usuário.

# Futuras etapas
Aprimorar o cálculo do engajamento considerando a separação de logs de apenas cliques e logs de interação na plataforma, como postagens, envio de atividades, notas, etc. 

Criar telas de engajamento somente de alunos e outra pra tutores/professores para se ter uma avaliação segmentada de diferentes perfis de usuários. 

Opção de fazer download de PDF e enviar o relatório por email para pessoas interessadas.

Configuração de alertas caso o engajamento de algum usuário esteja abaixo de um determinado threshold.

Visualização rápida dos módulos que o usuário não engajou ou engajou muito pouco em relação aos demais.


