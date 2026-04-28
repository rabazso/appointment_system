# Felhasználói dokumentáció

## 1. A rendszer rövid bemutatása

Az alkalmazás egy barbershop időpontfoglaló rendszer, ahol a vendégek online tudnak szolgáltatást, borbélyt és szabad időpontot választani. A rendszer kezeli a foglalásokat, az e-mailes megerősítést, a saját időpontok megtekintését, az értékeléseket, valamint külön felületet biztosít a borbélyoknak és az adminisztrátornak.

A projekt célja egy valós szalon működésének modellezése: a vendég gyorsan tudjon foglalni, a borbély átlássa a saját időpontjait, az admin pedig kezelni tudja az üzlet működéséhez szükséges adatokat.

Főbb funkciók:

- szolgáltatások és borbélyok megtekintése,
- vendégként vagy regisztrált felhasználóként történő foglalás,
- több szolgáltatás kiválasztása egy foglaláson belül,
- e-mailes foglalásmegerősítés,
- saját időpontok megtekintése és lemondása,
- befejezett időpont után értékelés írása,
- borbély oldali időpont- és profilkezelés,
- admin oldali szolgáltatás-, dolgozó-, nyitvatartás- és szabadságkezelés.

## 2. A rendszer elérése

Helyi fejlesztői környezetben a fontosabb címek:

- Frontend: `http://frontend.vm1.test`
- Backend API: `http://backend.vm1.test/api`
- phpMyAdmin: `http://pma.vm1.test`
- Mailcatcher: `http://mailcatcher.vm1.test`

A Mailcatcher felületén jelennek meg a fejlesztői környezetben kiküldött e-mailek, például a regisztrációs megerősítés, a foglalás visszaigazolása és a jelszó-visszaállító link.

## 3. Felhasználói szerepkörök

### 3.1 Vendég vagy ügyfél

A vendég böngészheti a nyilvános oldalakat, megtekintheti a szolgáltatásokat és a borbélyokat, majd időpontot foglalhat. A foglalás regisztráció nélkül is működik, ilyenkor a rendszer nevet és e-mail-címet kér. Regisztrált ügyfélként a felhasználó később meg tudja nézni és szükség esetén le tudja mondani a saját időpontjait.

Az ügyfél lehetőségei:

- időpontfoglalás,
- foglalás megerősítése e-mailben,
- saját foglalások listázása,
- aktív időpont lemondása,
- értékelés írása befejezett időpont után.

### 3.2 Borbély

A borbély külön belső felületen dolgozik. Itt látja a hozzá tartozó foglalásokat, kezelheti azok állapotát, szerkesztheti a publikus profilját, valamint szabadságkérelmet adhat le.

A borbély lehetőségei:

- saját időpontok megtekintése,
- időpont lemondása indoklással,
- időpont teljesítettnek vagy `no_show` állapotúnak jelölése,
- profilkép, bemutatkozás, linkek és galéria kezelése,
- saját beállítások megtekintése,
- szabadságkérelem leadása,
- saját értékelések megtekintése.

### 3.3 Adminisztrátor

Az adminisztrátor kezeli az üzlet működéséhez szükséges adatokat. Ő tud szolgáltatásokat, dolgozókat, nyitvatartást, szabadságokat, galériát és értékeléseket kezelni.

Az admin lehetőségei:

- szolgáltatások létrehozása, módosítása és törlése,
- dolgozók létrehozása és konfigurálása,
- dolgozókhoz szolgáltatások, árak és időtartamok rendelése,
- munkaidők, szünetek és foglalási szabályok kezelése,
- üzleti nyitvatartás és különleges napok beállítása,
- szabadságkérelmek elfogadása vagy elutasítása,
- foglalások szűrése és státuszkezelése,
- üzletadatok és galériaképek szerkesztése,
- értékelések láthatóságának módosítása.

## 4. Nyilvános oldalak

### 4.1 Kezdőlap

A kezdőlap bemutatja a szalont, a szolgáltatásokat, a borbélyokat és a galériát. Innen indítható a foglalás is. Ha a felhasználó nincs bejelentkezve, a rendszer megkérdezi, hogy vendégként szeretne-e továbbmenni, vagy inkább bejelentkezik.

### 4.2 Szolgáltatások

A szolgáltatások listája adatbázisból töltődik be. A demo rendszerben például ilyen szolgáltatások szerepelnek:

- Short haircut,
- Normal haircut,
- Long haircut,
- Beard trim,
- Fullbox,
- Father and son haircut,
- Brothers haircut.

Az ár és az időtartam borbélyonként eltérhet, mert az admin külön állíthatja be, hogy melyik dolgozó milyen szolgáltatást vállal.

### 4.3 Borbélyok és profiloldal

A `Barbers` oldalon megjelennek a borbélyok képpel, névvel, bemutatkozással és értékeléssel. A profiloldalon részletesebben látható a borbély leírása, elérhetősége, szolgáltatásai, galériája és értékelései.

### 4.4 Kapcsolat oldal

A `Contact` oldalon az üzlet publikus adatai láthatók: cím, telefonszám, e-mail-cím, nyitvatartás, linkek és térkép. Ezeket az admin a saját felületén módosíthatja.

## 5. Regisztráció és bejelentkezés

### 5.1 Regisztráció

A regisztráció a `Sign In` ablakból indítható a `Sign Up` lehetőséggel. A felhasználónak meg kell adnia a nevét, e-mail-címét és jelszavát. A jelszónak legalább 8 karakterből kell állnia, és tartalmaznia kell nagybetűt, kisbetűt, számot és speciális karaktert.

Regisztráció után a rendszer ellenőrző e-mailt küld. A fiók csak az e-mail-cím megerősítése után használható bejelentkezésre.

### 5.2 Bejelentkezés

Bejelentkezéshez e-mail-cím és jelszó szükséges. Ha a felhasználó még nem erősítette meg az e-mail-címét, a rendszer nem engedi belépni, és új hitelesítő linket küld.

### 5.3 Elfelejtett jelszó

A `Forgot password?` linkkel jelszó-visszaállítás kérhető. A felhasználó e-mailben kap egy linket, ahol új jelszót tud megadni.

## 6. Időpontfoglalás menete

A foglalás a kezdőlapról, a szolgáltatások részből, a borbélyok oldaláról vagy egy borbély profiloldaláról indítható.

A foglalás lépései:

1. Szolgáltatás vagy szolgáltatások kiválasztása.
2. Borbély kiválasztása.
3. Foglalható nap és szabad időpont kiválasztása.
4. Bejelentkezés vagy vendégadatok megadása.
5. Foglalás elküldése.
6. Foglalás megerősítése e-mailben.
7. Összegző oldal megtekintése.

A rendszer csak olyan borbélyokat ajánl fel, akik a kiválasztott szolgáltatásokat ténylegesen vállalják. A naptárban csak foglalható napok és szabad idősávok jelennek meg. A rendszer figyelembe veszi az üzlet nyitvatartását, a borbély munkaidejét, a szüneteket, a szabadságokat és a már meglévő foglalásokat.

Vendégként történő foglalásnál név és e-mail-cím megadása kötelező. Bejelentkezett ügyfélnél a rendszer a fiókhoz tartozó adatokat használja.

## 7. Foglalási szabályok és státuszok

Fontosabb foglalási szabályok:

- múltbeli dátumra nem lehet foglalni,
- csak elérhető szolgáltatás választható,
- csak szabad idősáv jelenik meg,
- ugyanahhoz a borbélyhoz ugyanarra az időpontra nem lehet duplán foglalni,
- szünetre, zárva tartásra vagy jóváhagyott szabadságra nem lehet foglalni,
- vendég e-mail-címe nem egyezhet már regisztrált felhasználó e-mail-címével,
- a foglalás csak e-mailes megerősítés után válik véglegessé.

Időpont státuszok:

- `pending`: a foglalás létrejött, de még megerősítésre vár,
- `confirmed`: a vendég e-mailben megerősítette a foglalást,
- `completed`: a szolgáltatás megtörtént,
- `cancelled`: az időpont le lett mondva,
- `no_show`: az ügyfél nem jelent meg.

## 8. Ügyfél felület

Bejelentkezett ügyfélként a `Your Appointments` oldalon láthatók a saját foglalások. Egy időpontnál megjelenik a szolgáltatás, borbély, dátum, időpont, időtartam, ár és státusz.

Az ügyfél a `pending` és `confirmed` állapotú időpontokat tudja lemondani. Befejezett, vagyis `completed` időpont után értékelést írhat. Egy időponthoz csak egy értékelés tartozhat.

## 9. Borbély felület

A borbély felület címe: `/employee/login`.

Főbb oldalak:

- `Your Appointments`: saját foglalások kezelése,
- `My Configuration`: szolgáltatások, munkaidő, elérhetőség és foglalási szabályok megtekintése,
- `Profile`: publikus profil, profilkép, galéria és linkek szerkesztése,
- `Time Off`: szabadságkérelmek leadása és követése,
- `Reviews`: saját értékelések megtekintése.

A borbély a jövőbeli időpontokat indoklással lemondhatja, a megtörtént időpontokat pedig `completed` vagy `no_show` állapotúra állíthatja.

## 10. Adminisztrátori felület

Az admin felület címe: `/admin/login`.

Főbb oldalak:

- `Appointments`: összes foglalás megtekintése, szűrése és státuszkezelése,
- `Services`: szolgáltatások létrehozása, módosítása és törlése,
- `Employees`: borbélyok kezelése és konfigurálása,
- `Schedule`: üzleti nyitvatartás és különleges napok kezelése,
- `Time Off`: szabadságkérelmek kezelése,
- `Reviews`: értékelések szűrése és láthatóságuk módosítása,
- `Shop Profile`: publikus üzletadatok és galéria szerkesztése.

Az admin a dolgozókhoz külön szolgáltatásokat, árakat, időtartamokat, munkaidőt, szüneteket és foglalási szabályokat rendelhet. A rendszer verziózott beállításokat használ, ezért egy módosítás megadható jövőbeli érvényességgel is.

## 11. Demo fiókok

### Admin

- E-mail: `admin@barbershop.test`
- Jelszó: `password`
- Bejelentkezés: `/admin/login`

### Borbélyok

Minden borbély jelszava: `password`.

- `blowout.ben@barbershop.test`
- `crispy.chris@barbershop.test`
- `bouncy.bella@barbershop.test`
- `loud.lucy@barbershop.test`
- `haircut.harry@barbershop.test`

Bejelentkezés: `/employee/login`.

## 12. Tipikus használati folyamatok

### Vendég foglalása

1. A vendég megnyitja a kezdőlapot.
2. Elindítja a foglalást.
3. A `Continue as Guest` lehetőséget választja.
4. Kiválasztja a szolgáltatást, borbélyt, dátumot és időpontot.
5. Megadja a nevét és e-mail-címét.
6. Elküldi a foglalást.
7. Megnyitja az e-mailben kapott megerősítő linket.

### Borbély napi munkája

1. A borbély belép az alkalmazotti felületen.
2. Megnézi a saját időpontjait.
3. Szükség esetén lemond egy jövőbeli foglalást.
4. A megtörtént időpontot lezárja `completed` állapottal.
5. Ha az ügyfél nem jelent meg, `no_show` státuszt állít be.

### Admin karbantartás

1. Az admin belép az admin felületen.
2. Ellenőrzi a foglalásokat és szabadságkérelmeket.
3. Szükség esetén módosítja a szolgáltatásokat vagy dolgozókat.
4. Frissíti a nyitvatartást, üzletadatokat vagy galériát.
5. Kezeli az értékelések láthatóságát.
