# Barbershop Appointment System

Figyelem! Mivel a Docker Compose és több container is igényt tart a `.env` fájlra, így az eredetileg a backendben lévő `.env.example` a projekt gyökerében található. Ezt kell lemásolni `.env` néven, majd igény szerint beállítani. Ezután a Docker Compose felcsatolja a szükséges containerekhez. 
## Indítás

A rendszer inicializálását és az első indítását a `start.sh` script végzi.

```bash
bash start.sh
```

Alapból a script **nem** futtat migrációt és seedinget.

Adatbázis migráció indításkor:

```bash
bash start.sh -m
```

Ugyanez hosszú kapcsolóval:

```bash
bash start.sh --migrate
```

Teljes adatbázis újraépítés és seedelés indításkor:

```bash
bash start.sh -fs
```

Ugyanez hosszú kapcsolóval:

```bash
bash start.sh --fresh-seed
```

Súgó:

```bash
bash start.sh --help
```

## Leállítás

```bash
docker compose stop
```

## Eltávolítás

```bash
docker compose down -v
```

 - A `-v` hatására a volume-okat is törli, így az adatbázisban tárolt adatok elvesznek.

## Felhasználók

**User / Vendég**
- Időpontot tudunk vendégként is foglalni, de ajánlott felhasználót csinálni. 
- Book now gomb megnymását követően tudjuk kiválasztani, hogy vendégként vagy a saját felhasználónkkal szeretnénk-e foglalni. Ha előbbit választjuk egyből tudunk foglalni időpontot és utána kell a szükséges adatokat megadni. Amennyiben az utóbbi megoldást választjuk be kell jelentkeznünk fiókunkba email cím és jelszó megadásával, ha nincs fiókunk még akkor a jobb sarokban található sing up gomb megnyomása után tudunk a szükséges adatok megadásával fiókot létrehozni.
- Userként tudunk időpontot foglalni, lemondani és megtudjuk tekinti azokat.

**Barberek**

- **Barber felhasználók email címei:**
    - blowout.ben@barbershop.test
    - crispy.chris@barbershop.test
    - bouncy.bella@barbershop.test
    - loud.lucy@barbershop.test
    - haircut.harry@barbershop.test

- **Barber felhasználók jelszavai:**
    - Minden barber felhasználó jelszava ugyan az: password

- A barberek látják a userek foglalásait (amennyiben hozzájuk foglalnak), ezeket megindokolva letudják mondani, tudnak szabadságot kérelmezni és tudnak időpontot foglalni magukhoz
- A barber fiókokba ugyan úgy a login gomb megnyomását követően a szükséges adatok megoldásával tudunk bejelentkezni. 

**Admin**

- **Admin felhasználó email címe:**
    - admin@barbershop.com

- **Barber felhasználók jelszavai:**
    - Az admin jelszava: password

- Az admin képes változtatni az árakat, a nyitvatartást, hozzátud adni új barbereket és a barberekhez hozzá tud adni és el tud venni szolgáltatásokat, illetve tudja engedélyezni és elutasítani a szabadság kérelmeket.
- Az admin fiókokba ugyan úgy a login gomb megnyomását követően a szükséges adatok megoldásával tudunk bejelentkezni. 

**Figma**

- **Linkek**
    - https://www.figma.com/design/fkwYRHOaqkNhVo9R4FrSKF/Admin-and-Employee-pages?node-id=8-997&t=TP6Fy3jCglv6U5nU-1
    - https://www.figma.com/design/RCk9qOHnUlyXwd3OEAtfnp/Appointment_system?node-id=0-1&t=RvMz3niXW4j1vxl2-1
    - https://www.figma.com/design/hNLsYS1BujHypjAPD2R9ls/Appointment_system_barbersPage?node-id=0-1&t=QL1vbQrz0hs0erJ6-1
    - https://www.figma.com/design/N1b9XKxpfRqADkj0BSPut2/Appointment_system_Contacts?node-id=0-1&t=JKIQJ3ntb9OVvsOd-1
    - https://www.figma.com/design/RCk9qOHnUlyXwd3OEAtfnp/Appointment_system?node-id=106-2&t=42EUqwyHLpQoSOQw-1
    - https://www.figma.com/design/OExiQWelH4z2fcJBK4kptC/Appointment_system_mobile_view?node-id=0-1&t=ipiEFPSaM4f2PNXq-1
