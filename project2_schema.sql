CREATE TABLE Customers (
    username VARCHAR2(10) PRIMARY KEY,
    password VARCHAR2(50) NOT NULL,
    first_name VARCHAR2(50) NOT NULL,
    last_name VARCHAR2(50) NOT NULL,
    phone VARCHAR2(15) NOT NULL,
    customer_type CHAR(1) CHECK (customer_type IN ('D' , 'F')) NOT NULL,
    diamond_status NUMBER(1) DEFAULT 0 CHECK (diamond_status IN (0, 1)),
    state CHAR(2),
    country CHAR(2),
    CHECK (
        (customer_type = 'D' AND state IS NOT NULL AND country IS NULL) OR
        (customer_type = 'F' AND country IS NOT NULL AND state IS NULL)
    )
);



CREATE TABLE Flight_Routes (
    airline_name CHAR(2) NOT NULL,
    flight_number NUMBER(4) NOT NULL,
    start_date DATE NOT NULL,
    PRIMARY KEY (airline_name, flight_number)
);


CREATE TABLE Flights (
    airline_name CHAR(2) NOT NULL,
    flight_number NUMBER(4) NOT NULL,
    flight_id NUMBER(4) PRIMARY KEY,
    flight_date DATE NOT NULL,
    capacity NUMBER NOT NULL,
    FOREIGN KEY (airline_name, flight_number) 
        REFERENCES Flight_Routes(airline_name, flight_number)
);



CREATE TABLE Preceding_Routes (
    airline_name CHAR(2) NOT NULL,
    flight_number INT NOT NULL,
    preceding_airline CHAR(2) NOT NULL,
    preceding_flight_number INT NOT NULL,
    PRIMARY KEY (airline_name, flight_number, preceding_airline, preceding_flight_number),
    FOREIGN KEY (airline_name, flight_number) 
        REFERENCES Flight_Routes(airline_name, flight_number),
    FOREIGN KEY (preceding_airline, preceding_flight_number) 
        REFERENCES Flight_Routes(airline_name, flight_number)
);

CREATE TABLE Reservations (
    username VARCHAR2(10) NOT NULL,
    flight_id NUMBER(4) NOT NULL,
    seating_grade NUMBER(1) DEFAULT 0,
    PRIMARY KEY (username, flight_id),
    FOREIGN KEY (username) REFERENCES Customers(username),
    FOREIGN KEY (flight_id) REFERENCES Flights(flight_id)
);


CREATE TABLE Users (
  username VARCHAR2(255) PRIMARY KEY,
  password VARCHAR2(255) NOT NULL,
  first_name VARCHAR2(255) NOT NULL,
  last_name VARCHAR2(255) NOT NULL
);

-- Step 2: Add start_date and registration_date columns to the User table
ALTER TABLE Users ADD start_date DATE;
ALTER TABLE Users ADD registration_date DATE;

-- Step 3: Create the Role table
CREATE TABLE Role (
  role_id NUMBER PRIMARY KEY,
  role_name VARCHAR2(255) NOT NULL UNIQUE
);

-- Step 4: Create the UserRole table
CREATE TABLE UserRole (
  username VARCHAR2(255),
  id_role NUMBER,
  PRIMARY KEY (username, id_role),
  FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE,
  FOREIGN KEY (id_role) REFERENCES Role(role_id) ON DELETE CASCADE
);

-- Step 5: Create the Usersession table
CREATE TABLE Usersession (
  sessionid VARCHAR2(255) PRIMARY KEY,
  username VARCHAR2(255),
  sessiondate DATE,
  FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE
);


-- Domestic customers
INSERT INTO Customers VALUES ('JD1001', 'password123', 'John', 'Doe', '555-123-4567', 'D', 0, 'CA', NULL);
INSERT INTO Customers VALUES ('AS1002', 'pass456', 'Alice', 'Smith', '555-234-5678', 'D', 1, 'NY', NULL);
INSERT INTO Customers VALUES ('RJ1003', 'rjpass', 'Robert', 'Johnson', '555-345-6789', 'D', 0, 'TX', NULL);

-- Foreign customers
INSERT INTO Customers VALUES ('MB1004', 'mbpass', 'Maria', 'Brown', '555-456-7890', 'F', 0, NULL, 'UK');
INSERT INTO Customers VALUES ('DW1005', 'dwpass', 'David', 'Wilson', '555-567-8901', 'F', 1, NULL, 'CA');


INSERT INTO Flight_Routes VALUES ('AA', 100, TO_DATE('2023-01-01', 'YYYY-MM-DD'));
INSERT INTO Flight_Routes VALUES ('DL', 200, TO_DATE('2023-02-15', 'YYYY-MM-DD'));
INSERT INTO Flight_Routes VALUES ('UA', 300, TO_DATE('2023-03-10', 'YYYY-MM-DD'));
INSERT INTO Flight_Routes VALUES ('BA', 400, TO_DATE('2023-04-05', 'YYYY-MM-DD'));
INSERT INTO Flight_Routes VALUES ('LH', 500, TO_DATE('2023-05-20', 'YYYY-MM-DD'));







--1)Sequential Username



CREATE SEQUENCE customer_seq
  START WITH 1001
  INCREMENT BY 1
  NOCACHE;


$sql = "SELECT customer_seq.NEXTVAL FROM dual";


--2)his is Triggers, where any update done to Reservations, it should go through the diamond status.

CREATE OR REPLACE TRIGGER trg_update_diamond_status
AFTER INSERT OR UPDATE ON Reservations
DECLARE
  CURSOR c_users IS
    SELECT DISTINCT username FROM Reservations;
  v_total NUMBER;
  v_sum NUMBER;
  v_score NUMBER;
BEGIN
  FOR user_rec IN c_users LOOP
    SELECT COUNT(*), NVL(SUM(seating_grade), 0)
    INTO v_total, v_sum
    FROM Reservations
    WHERE username = user_rec.username;

    IF v_total = 0 THEN
      v_score := 0;
    ELSE
      v_score := v_sum / v_total;
    END IF;

    UPDATE Customers
    SET diamond_status = CASE WHEN v_score >= 1 THEN 1 ELSE 0 END
    WHERE username = user_rec.username;
  END LOOP;
END;
/
T




--3)View

CREATE OR REPLACE VIEW flight_view AS
SELECT 
    f.flight_id,
    f.airline_name,
    f.flight_number,
    f.flight_date,
    f.capacity,
    f.capacity - NVL(r.count_reserved, 0) AS available_seats
FROM Flights f
LEFT JOIN (
    SELECT flight_id, COUNT(*) AS count_reserved
    FROM Reservations
    GROUP BY flight_id
) r ON f.flight_id = r.flight_id
WHERE f.flight_date >= TRUNC(SYSDATE);


--4)Stored Procedure for Flight Reservation


CREATE OR REPLACE PROCEDURE ReserveSingleFlight(
    p_username IN Customers.username%TYPE,
    p_flight_id IN Flights.flight_id%TYPE
)
IS
    v_flight_date Flights.flight_date%TYPE;
    v_capacity Flights.capacity%TYPE;
    v_booked NUMBER;
    v_exists NUMBER;
BEGIN
    -- Check if flight exists and get flight date
    SELECT flight_date, capacity
    INTO v_flight_date, v_capacity
    FROM Flights
    WHERE flight_id = p_flight_id;

    -- Check if flight is in the past
    IF v_flight_date < TRUNC(SYSDATE) THEN
        RAISE_APPLICATION_ERROR(-20001, 'Cannot reserve a flight in the past.');
    END IF;

    -- Check booked seats
    SELECT COUNT(*)
    INTO v_booked
    FROM Reservations
    WHERE flight_id = p_flight_id;

    IF v_booked >= v_capacity THEN
        RAISE_APPLICATION_ERROR(-20002, 'No available seats on the flight.');
    END IF;

    -- Check if already reserved
    SELECT COUNT(*)
    INTO v_exists
    FROM Reservations
    WHERE username = p_username
      AND flight_id = p_flight_id;

    IF v_exists > 0 THEN
        RAISE_APPLICATION_ERROR(-20003, 'You have already reserved this flight.');
    END IF;

    -- Insert reservation
    INSERT INTO Reservations(username, flight_id, seating_grade)
    VALUES (p_username, p_flight_id, 0);

    COMMIT;
END ReserveSingleFlight;
/

