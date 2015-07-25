#### Eingänge
- procedure = der Name der `stored procdure`
- params = Aufrufparameter einer `stored procedure`

| Bezeichnung  | Eingabetyp  | Ausgabetyp | Befehl | Beschreibung |
| :----------- |:-----------:| :---------:| :----- | :----------- |
|postQuery|Query|Query|POST<br>/query| führt eine SQL Anfrage aus, welche im `request` Teil des Eingabeobjekts angegeben wurde  |
|postMultiGetRequest|-|-|POST<br>/multiGetRequest| führt eine Liste von Anfragen aus (nur GET Anfragen) |
|getProcedureQuery|-|Query|GET<br>/query/procedure/:procedure(/:params+)| ruft eine `stored procedure` mit den angegebenen Parametern auf |
|addPlatform|Platform|Platform|POST<br>/platform|installiert dies zugehörige Tabelle und die Prozeduren für diese Plattform|
|deletePlatform|-|Platform|DELETE<br>/platform|entfernt die Tabelle und Prozeduren aus der Plattform|
|getExistsPlatform|-|Platform|GET<br>/link/exists/platform| prüft, ob die Tabelle und die Prozeduren existieren |

#### Anbindungen
| Bezeichnung  | Ziel  | Priorität | Beschreibung |
| :----------- |:----- | :--------:| :------------|
|request|CLocalObjectRequest|-| damit DBQuery als lokales Objekt aufgerufen werden kann |

Stand 29.06.2015