****************************************************
Módulo de Integração do WiseCP para a API VirtFusion
****************************************************

INTRODUÇÃO

O sistema de faturação WiseCP tem várias integrações disponíveis, incluindo, entre outras, SolusVM 2, Virtualizor e AutoVM. Contudo, até ao momento, não dispunha de uma integração que permitisse o aprovisionamento de VPS em VirtFusion, seja através de um módulo providenciado pelo próprio WiseCP - como várias vezes acontece - seja através de um módulo que fosse providenciado pelo VirtFusion, enquanto natural interessado em abarcar este mercado.

A integração agora pronta e disponível colmata esta situação, permitindo que o sistema de faturação WiseCP permita agora um aprovisionamento e gestão automáticas face a VPS aprovisionadas a partir de centros de gestão (management nodes) do VirtFusion, simplificando substancialmente a vida a Clientes, provedores e suporte.

PORQUÊ DESENHAR UMA INTEGRAÇÃO PARA VIRTFUSION

O sistema VirtFusion é um dos sistemas de aprovisionamento e gestão mais utilizados neste setor, sendo também o sistema que reúne as preferências dos consumidores que adquirem este tipo de serviços (VPS/VDS), bem como de muitos provedores de serviços. De entre os três principais disponíveis - SolusVM, Virtualizor e VirtFusion - este dispõe de algumas vantagens, designadamente a abordagem madura a um conjunto de problemas comuns a provedores e Clientes; soluções testadas e relativamente fáceis de compreender e implementar para quem disponha de um mínimo de conhecimentos em administração de sistemas Linux; uma interface que do lado do Cliente é simples, fácil de utilizar e direta; e para o provedor é particularmente bem elaborada, tendo um conjunto de funcionalidades exclusivas ao sistema que melhoram a gestão possível e o produto final ao Cliente.

Por sua vez, o sistema WiseCP, de entre os sistemas de faturação atualmente disponíveis no mercado, é um dos mais poderosos e resilientes, competindo em vários aspetos com o atual líder do setor e possuindo uma interface extremamente apelativa e com bastante funcionalidade.

Desenhar uma integração entre ambos os sistemas é, por isso, algo perfeitamente natural e razoável, dado que ambos convergem numa experiência de utilização consistente e melhorada para o Cliente, com elevada qualidade de serviço, elevada disponibilidade e fácil gestão dos diversos elementos necessários a ambas as partes.

COMPATIBILIDADE E FUNCIONALIDADES DESTE MÓDULO

O módulo aqui desenhado integra primordialmente funções provenientes da API v1.0 do VirtFusion, que se encontra em utilização e contínua ampliação de funcionalidades (à data de 01-06-2026), e está testado para funcionar em WiseCP nas versões 3.1.9.7, 3.1.9.8 e 3.1.9.8.1, com PHP 8.2, 8.3, 8.4 e 8.5 (de forma 100% compatível e sem erros), e em VirtFusion 6.1+ e 7.0+.

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
        - Tamanho do 1º Disco Rígido alocado no Sistema (complementado pela informação nas Especificações WiseCP);
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


