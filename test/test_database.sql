DROP TABLE reservations;

# Create table reservations
CREATE TABLE IF NOT EXISTS reservations
(
    id               INT(6) ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(30)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    phone            VARCHAR(20)     NOT NULL,
    address          VARCHAR(80)     NOT NULL,
    date             DATE            NOT NULL,
    reservation_date DATE            NOT NULL,
    from_time        TIME            NOT NULL,
    to_time          TIME            NOT NULL,
    single_kajak     NUMERIC         NOT NULL,
    double_kajak     NUMERIC         NOT NULL,
    archived         BOOLEAN         NOT NULL DEFAULT FALSE,
    cancelled        BOOLEAN         NOT NULL DEFAULT FALSE,
    CONSTRAINT NAME_CHECK CHECK (REGEXP_LIKE(name, '^[A-ZäÄöÖüÜßa-z]+ [A-ZäÄöÖüÜßa-z]+$'))
);

# Fill table reservations'
INSERT INTO reservations (name, email, phone, address, date, reservation_date, from_time, to_time, single_kajak, double_kajak, archived, cancelled)
VALUES ('Paul Aner', 'lol@123.de', 'Max-Straße 8', '123456789', '2019-01-01', '2022-12-01', '10:00:00', '11:00:00', 1, 0, FALSE, FALSE),
       ('Spe Zi', 'foo@bar.de', 'Max-Straße 9', '987654321', '2019-01-01', '2018-12-02', '9:00:00', '14:00:00', 1, 0, FALSE, FALSE),
       ('Scheiß Verein', '123@123.de', 'Nice-Straße 1', '123498765', '2019-01-02', '2016-10-13', '12:00:00', '15:00:00', 1, 0, FALSE, FALSE),
       ('Olivia Bolivia', '123@123.de', 'Nice-Straße 1', '123498765', '2019-01-02', '2016-10-13', '12:00:00', '15:00:00', 1, 0, FALSE, FALSE);

# Query table reservations
SELECT *
FROM reservations;

SELECT *
FROM reservations
WHERE date = '2019-01-01';

SELECT SUM(single_kajak) as amount
FROM reservations
WHERE date = '2022-04-8'
  AND (reservations.from_time BETWEEN '9:00:00' AND '17:59:59'
    OR reservations.to_time BETWEEN '9:00:00' AND '17:59:59');

SELECT email
FROM reservations
WHERE id = 1;