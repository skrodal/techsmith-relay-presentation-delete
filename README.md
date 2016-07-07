# TechSmith Relay - Delete presentations #

_This API is tailor-made for UNINETT AS for a specific use-case. Its re-usability is therefore limited._

Dette er en enkel Web Service som har som hensikt å informere et (cron)script om hvilke presentasjonsmapper fra TechSmith Relay 
som kan flyttes/slettes/gjenopprettes på filserver.

Informasjon hentes/skrives til en tabell på UNINETTs (my)SQL Cluster. Denne tabellen populeres av et annet API som benyttes av sluttbrukerklienter, 
der sluttbruker kan be om å slette/gjenopprette sitt innhold. 

Scriptet som snakker med denne WebServic'en har flere oppgaver:

## 1: Periodisk sjekk av forespørsler om sletting ##

Scriptet må sjekke periodisk om sluttbrukere har lagt til presentasjoner (paths) i tabell som skal slettes. I første omgang vil ikke 
presentasjoner slettes permanent. Dette for å gi sluttbruker noen dager til å angre før de slettes permanent (beskrevet lenger ned).

Scriptet må derfor _flytte_ paths til et midlertidig område på disk som _ikke_ er tilgjengelig fra web.

1.  Hent liste over presentasjoner (paths) som skal flyttes bort fra brukermappe til et område utilgjengelig fra web:

        curl -X "GET" "https://____server____/techsmith-relay-presentation-delete/presentations/movable/" \
            -H "token: ___TOKEN___"

    - Eksempelsvar (`presentations` er en array med 0-mange presentasjonsobjekter), der kun `path` er av interesse i svaret:
     
            {
              "status": true,
              "info": "Presentations ready to be moved from user folder.",
              "presentations": [
                {
                  "id": "5",
                  "timestamp": "2016-07-07 12:16:18",
                  "path": "ansatt/simonuninett.no/2014/27.09/10333/",
                  "username": "simon@uninett.no",
                  "moved": "0",
                  "deleted": "0",
                  "undelete": "0"
                },
                {
                  ...osv
                }
              ]
            }

    - Script må nå flytte alle paths i svar fra WebService bort fra område tilgjengelig via web 
    
2. Når paths i lista er ferdig flyttet, kjør et nytt kall til WebService for å oppdatere tabell med hvilke presentasjoner som ble flyttet:
    
        curl -X "POST" "https://____server____/techsmith-relay-presentation-delete/presentations/update/move/" \
            -H "token: ___TOKEN___" \
            -H "Content-Type: text/plain; charset=utf-8" \
            -d $'{
          "presentations": [
            {
              "path": "ansatt/simonuninett.no/2014/27.09/10333/",
            },
            {
              ...osv
            }
          ]
        }'

    - Dette vil sette `moved` til 1 for disse presentasjonene i tabell.

## 2: Periodisk sjekk av forespørsler om gjenoppretting (angre) ##
 
 Scriptet må sjekke periodisk om det er kommet inn forespørsler om flyttede presentasjoner som skal gjenopprettes (flyttes tilbake til bruker's folder). 
 Slike presentasjoner har flagg `moved` og `undelete` satt til 1 i tabellen.
 
1.  Hent liste over presentasjoner (paths) som skal flyttes tilbake til brukermappe fra et område utilgjengelig fra web
    
            curl -X "GET" "https://____server____/techsmith-relay-presentation-delete/presentations/undeletable/" \
                -H "token: ___TOKEN___"

    - Eksempelsvar (`presentations` er en array med 0-mange presentasjonsobjekter), der kun `path` er av interesse i svaret:
    
        {
          "status": true,
          "info": "Presentations to be undeleted from the file system, i.e. moved back to its original location ('path'). When the presentation(s) are restored, send a DELETE request to '/presentations/delete/' with the returned response from this query to remove the entry from the DB.",
          "presentations": [
            {
              "id": "4",
              "timestamp": "2016-06-30 13:16:18",
              "path": "ansatt/simonuninett.no/2015/12.04/116700/",
              "username": "simon@uninett.no",
              "moved": "1",
              "deleted": "0",
              "undelete": "1"
            }
          ]
        }
    
    - Script må nå flytte alle paths i lista tilbake til bruker's folder

2. Når #1 er ferdig gjennomført, må scriptet opptatere tabell (SLETTe rader) med info om presentasjoner som ble flyttet tilbake:

        curl -X "DELETE" "https://____server____/techsmith-relay-presentation-delete/records/delete/" \
            -H "token: ___TOKEN___" \
            -H "Content-Type: text/plain; charset=utf-8" \
            -d $'{
          "presentations": [
            {
              "path": "ansatt/simonuninett.no/2015/12.04/116700/"
            }
          ]
        }'


    - Kommando over vil altså fjerne disse presentasjonene fra tabellen.

## 3: Periodisk _permanent_ sletting av presentasjoner flyttet for >14 dager siden ##

Presentasjoner som ble flyttet av scriptet (se #1) vil ligge i et utilgjengelig område i 14 dager før de kan slettes permanent. 
Innenfor disse 14 dagene kan sluttbruker angre på sletting og flytte innhold tilbake igjen (se #2). Etter 14 dager er det for 
altså for sent å angre...  

Scriptet må periodisk (døgnlig) sjekke hvilke presentasjoner som ble flyttet > 14 dager siden og slette disse permanent:

1.  Hent liste over presentasjoner (paths) som skal slettes:

        curl -X "GET" "https://____server____/techsmith-relay-presentation-delete/presentations/deletable/" \
            -H "token: ___TOKEN___"

    - Eksempelsvar (`presentations` er en array med 0-mange presentasjonsobjekter), der kun `path` er av interesse i svaret:
    
            {
              "status": true,
              "info": "Presentations ready to be deleted from the file system (i.e. older than 14 days.)",
              "presentations": [
                {
                  "id": "3",
                  "timestamp": "2016-06-23 13:16:18",
                  "path": "ansatt/simonuninett.no/2014/11.06/186700/",
                  "username": "simon@uninett.no",
                  "moved": "1",
                  "deleted": "0",
                  "undelete": "0"
                }
              ]
            }

    - Script må nå _permanent_ slette alle paths i svar fra WebService.
    
2. Når #1 er ferdig gjennomført og presentasjoner er slettet, må scriptet opptatere tabell (markere presentasjoner som `deleted`):

        curl -X "DELETE" "https://____server____/techsmith-relay-presentation-delete/records/delete/" \
            -H "token: ___TOKEN___" \
            -H "Content-Type: text/plain; charset=utf-8" \
            -d $'{
          "presentations": [
            {
              "path": "ansatt/simonuninett.no/2015/12.04/116700/"
            }
          ]
        }'

    - Presentasjonene blir da markert som slettet i tabellen.

## Tilgang ##

Bruker ikke Dataporten, men heller en `token` som sendes med i header for hvert kall.

## Tabell ##

Bruker UNINETTs MySQL server, eks:

     DROP TABLE `presentations_deletelist`;
        
        CREATE TABLE `presentations_deletelist` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `path` text NOT NULL,
          `username` varchar(40) NOT NULL DEFAULT '',
          `moved` tinyint(1) unsigned NOT NULL DEFAULT 0,
          `deleted` tinyint(1) unsigned NOT NULL DEFAULT 0,
          `undelete` tinyint(1) unsigned NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
        					
Se `\Routes_GET::restoreSampleData` for eksempeldata.


### Annet ###

Presentasjoner legges til i tabell for sletting av sluttbruker selv via dette APIet (bruker Dataporten): 

- https://github.com/skrodal/techsmith-relay-api
                                                                                       
APIet over brukes bl.a. av følgende klient (RelayAdmin, bruker også Dataporten):

- https://github.com/skrodal/techsmith-relay-admin

Utviklet av Simon Skrødal