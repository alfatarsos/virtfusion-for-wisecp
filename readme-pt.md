****************************************************
Módulo de Integração do WiseCP para a API VirtFusion
****************************************************

INTRODUÇÃO

O sistema de faturação WiseCP tem várias integrações disponíveis, incluindo, entre outras, SolusVM 2, Virtualizor e AutoVM. Contudo, até ao momento, não dispunha de uma integração que permitisse o aprovisionamento de VPS em VirtFusion, seja através de um módulo providenciado pelo próprio WiseCP - como várias vezes acontece - seja através de um módulo que fosse providenciado pelo VirtFusion, enquanto natural interessado em abarcar este mercado.

A integração agora pronta e disponível colmata esta situação, permitindo que o sistema de faturação WiseCP permita agora um aprovisionamento e gestão automáticas face a VPS aprovisionadas a partir de centros de gestão (management nodes) do VirtFusion, simplificando substancialmente a vida a Clientes, provedores e suporte.

PORQUÊ DESENHAR UMA INTEGRAÇÃO PARA VIRTFUSION

O sistema VirtFusion é um dos sistemas de aprovisionamento e gestão mais utilizados neste setor, sendo também o sistema que reúne as preferências dos consumidores que adquirem este tipo de serviços (VPS/VDS), bem como de muitos provedores de serviços. De entre os três principais disponíveis - SolusVM, Virtualizor e VirtFusion - este dispõe de algumas vantagens, designadamente a abordagem madura a um conjunto de problemas comuns a provedores e Clientes; soluções testadas e relativamente fáceis de compreender e implementar para quem disponha de um mínimo de conhecimentos em administração de sistemas Linux; uma interface que do lado do Cliente é simples, fácil de utilizar e direta; e para o provedor é particularmente bem elaborada, tendo um conjunto de funcionalidades exclusivas ao sistema que melhoram a gestão possível e o produto final ao Cliente.

Por sua vez, o sistema WiseCP, de entre os sistemas de faturação atualmente disponíveis no mercado, é um dos mais poderosos e resilientes, competindo em vários aspetos com o atual líder do setor e possuindo uma interface extremamente apelativa e com bastante funcionalidade.

Desenhar uma integração entre ambos os sistemas é, por isso, algo perfeitamente natural e razoável, dado que ambos convergem numa experiência de utilização consistente e melhorada para o Cliente, com elevada qualidade de serviço, elevada disponibilidade e fácil gestão dos diversos elementos necesśarios a ambas as partes.

COMPATIBILIDADE E FUNCIONALIDADES DESTE MÓDULO

O módulo aqui desenhado integra primordialmente funções provenientes da API v1.0 do VirtFusion, que se encontra em utilização e contínua ampliação de funcionalidades (à data de 09-12-2025), e está apenas testado para funcionar, pelo menos, com a versão mais recente do WiseCP (v3.1.9.7, lançada em Novembro de 2025).

Embora não seja expectável que haja problemas com outras versões mais antigas do WiseCP (3.1.9.x), nem com a versão 5.0 em desenvolvimento - desde que o WiseCP não quebre a lógica das integrações já existentes para o ecossistema com outro tipo de codificação requerido - não se garante a compatibilidade com qualquer outra versão dado que só foi testado nesta, à presente data - teste por conta e risco.

Se e quando vier a confirmar-se compatibilidade de próximas versões, este ficheiro será atualizado em tempo útil.

As funcionalidades concluídas e agora disponíveis incluem:

- Aprovisionamento automático de VPS em apenas 20 segundos;

- Suspensão automática de VPS em até 60 segundos, dependentes de atualização do crontab PHP em WiseCP;

- Cancelamento de VPS, inserido de imediato no sistema VirtFusion, e executado após 5 minutos, conforme predefinição da API;

- Integração da mais recente funcionalidade de VNC na Área de Cliente para o Utilizador, criada em Novembro, conforme à nova versão VirtFusion 6.1, através de extração da UUID e formatação do caminho no navegador de forma automática;

- Informação na Área de Cliente do estado da sua VPS: Online / Em Aprovisionamento / Offline;

- Feedback na Área Cliente da execução de operações (Iniciar / Reiniciar / Encerrar / Forçar Paragem);

- Ligação direta ao Painel VirtFusion por link externo, em 2 botões (geral no botão "Painel", para password reset no botão respetivo) - útil para temas como gestão de NAT ou fine-tuning;

- Comunicação direta na Área de Cliente para recuperação de password de acesso ao Painel sem necessitar de e-mail, diretamente no ecrã, para comodidade;

- Informação sumária ao Cliente através de parâmetros dinâmicos por call à API, designadamente de:

        - Hostname associado em sistema no VirtFusion;
        - Localização principal (função HypervisorGroup no VirtFusion) e IP principal (predefine a IPv6 em qualquer subnet; caso não haja, assume IPv4)
        - IPv4, IPv4 NAT e IPv6 disponíveis, em todas as formulações e gamas, até em IPv6 /128;
        - IP de origem do servidor dedicado, útil em NAT para o utilizador;
        - Largura de Banda Disponível, expressa em GB;
        - Número de vCPUs alocado no Sistema (complementado pela informação nas Especificações WiseCP)
        - GB de memória alocada no Sistema (complementado pela informação nas Especificações WiseCP)
        - Disco Rígido alocado no Sistema (complementado pela informação nas Especificações WiseCP);
        - Sistema Operativo alocado no Sistema, parâmetro dinâmico;
        - Nome do Servidor Dedicado a que o Cliente ficou alocado (reforço da informação na barra de título);
        - Localização principal (reforço da informação na barra de título).
        
- Operação certificada e fácil de executar em dispositivos móveis, com botões grandes de várias cores, fáceis de tocar e de fácil representação, que se adaptam naturalmente ao formato 1:1 destes dispositivos;

- Backoffice suporta as seguintes opções em Product Settings » Core:

        - Definir Plano de Serviço a aplicar ao Cliente
        - Ativar/Desativar IPv6 (opção assim fornecida pela API VirtFusion) - definição de número de IPs tem que ser feita em BO VirtFusion pelo provedor, aplicada ao Plano de Serviço para o qual vamos realizar deployment, no Painel de Controlo respetivo;
        - Número de IPv4s a Adicionar (valor entre 0 e 4, permite aprovisionamento IPv6-only, IPv4 NAT ou Full IPv4);
        - Location ID manual (sistema de fallback caso não seja possível obter HypervisorGroup);
        - Localização principal (função de HypervisorGroup para deployment - com opção Auto para a 1ª localização escolhida pelo VirtFusion).
        
- Interface muito apelativa, gradativamente colorida, fácil de utilizar para o Cliente e com mais funções sem sair da Área de Cliente face a qualquer das integrações atualmente existentes de VirtFusion para WHMCS, Blesta, ClientExec, HostBill, Upmind e Paymenter - conforme é preferência normal e natural do Cliente e conforme já teria habitualmente noutros sistemas (por exemplo Virtualizor, SolusVM e Proxmox).

NOTAS TÉCNICAS E BUGS CONHECIDOS

Naturalmente, integrar algo assim no sistema WiseCP tem desafios e particularidades, como acontece com qualquer integração. As existentes para este módulo não impedem a regular operação do mesmo, mas devem ser mencionadas.

- SSO só acontece caso cliente já esteja com login realizado, por motivos de segurança e diferentes abordagens ao SSO por parte de VirtFusion e WiseCP. Se não estiver, é pedido o login prévio, e logins subsequentes manter-se-ão ativos até que o cookie instalado expire ou o cliente faça logoff da área VirtFusion.
- VNC só funciona se cliente tiver feito login previamente no VirtFusion. Se não o tiver feito, será aberta uma janela a pedi-lo.
- O campo de "Definir Password do Painel", que não precisa de e-mail, obriga a que o cliente tome nota manualmente da chave mostrada, dado que o campo não permite edição. Isto opera assim por defeito mas também é uma medida de segurança (impede clipboard hijacking de um dado sensível que pode comprometer a segurança do cliente).
- As ligações externas ao Painel e "Reset de Password" dependem de, no IP em WiseCP, estar definido novamente o Hostname. Caso contrário, apenas aparecerá o IP e a ligação não se estabelece com SSL. É por isso essencial que a configuração esteja bem realizada internamente pelo provedor.
- A comunicação das funções de Power (ligar/desligar/reiniciar/forçar paragem) e estado da VPS no módulo, por limitações derivadas da comunicação interna no sistema WiseCP, não estão em sintonia direta com o sistema VirtFusion, mas são trabalhadas semidiretas, de acordo com um sistema de "educated guess", em que foi realizada a verificação do tempo máximo razoável para cada uma das operações e é dado o tempo para que elas completem na VPS respetiva (25 segundos em Start/Restart, 40 segundos em Shutdown/Power Off). O cliente ou o Provedor poderá sempre confirmar o estado exato da VPS através de VNC, ou iniciando sessão no Painel VirtFusion para executar a gestão respetiva - isto ajudará com qualquer erro técnico.
- Para assegurar a comunicação correta das funções de Power e fiabilidade na experiência final para o Cliente, é obrigatório observar pelo Provedor no mínimo uma velocidade de disco rígido superior a 80 MB/s em 64k ou superior, 40 MB/s em 4k, e evitar uma restrição excessivamente significativa de performance em vCPUs, ou iowait superior a 25%. A falha na observação de um destes parâmetros por parte do Provedor piorará substantivamente a experiência ao Cliente na interação com a VPS na sua Área de Cliente.
- A indicação no sistema WiseCP para a VPS adquirida, acima da informação apresentada neste módulo, será sempre de "Online" (a verde) e deve ser interpretada como o pacote adquirido em si estar ativo no sistema de faturação do website, não representando qualquer verificação válida de estado da VPS. Por limitações de ordem técnica, este estado apenas se apresenta no WiseCP como "Online", "Obtaining..." ou vazio (em casos de suspensão).
- A função de Manual Location ID (em Core) não está testada previamente.
- Caso se pretenda colocar mais que 1 disco rígido num dado Plano de Serviço, este não é aprovisionado neste módulo, mas sim no próprio Plano de Serviço junto do Management Node do VirtFusion, na área adequada. Por opção técnica, não foi ativada esta opção, apesar de exposta na API, uma vez que se entendeu ser suficiente a configuração por plano e os discos alocados são frequentemente fixos nos seus caminhos, mesmo em situações de High Availability.
- A atribuição de mais que 1 endereço IPv6 no aprovisionamento não é suportada por limitações da API de VirtFusion. Caso o Cliente pretenda mais que um endereço ou uma subnet range superior, isto deverá ser gerido no próprio Management Node.

DISPONIBILIZAÇÃO DO MÓDULO

Este módulo já se encontra disponível para compra, desde 22-12-2025, a partir deste website (em Inglês): https://web.c-servers.co.uk/category/virtfusion-integration-module-for-wisecp
