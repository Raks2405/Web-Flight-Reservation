-- Step 1: Create the User table (without start_date and registration_date)
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
  role_id NUMBER,
  PRIMARY KEY (username, role_id),
  FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES Role(role_id) ON DELETE CASCADE
);

-- Step 5: Create the Usersession table
CREATE TABLE Usersession (
  sessionid VARCHAR2(255) PRIMARY KEY,
  username VARCHAR2(255),
  sessiondate DATE,
  FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE
);
 

INSERT INTO Role (role_id, role_name) VALUES (1, 'RegularUser');
INSERT INTO Role (role_id, role_name) VALUES (3, 'Hybrid User');

INSERT INTO Role (role_id, role_name) VALUES (2, 'Admin');


INSERT INTO Users (username, password, first_name, last_name, registration_date)
VALUES('JD1001', 'password123', 'John', 'Doe',  TO_DATE('2023-10-01', 'YYYY-MM-DD'));
INSERT INTO UserRole(username, role_id) VALUES ('JD1001', 1);

INSERT INTO Users (username, password, first_name, last_name, registration_date)
VALUES ('john_doe', '1234', 'John', 'Doe', TO_DATE('2023-10-01', 'YYYY-MM-DD'));
INSERT INTO UserRole (username, role_id) VALUES ('john_doe', 1);

INSERT INTO Users (username, password, first_name, last_name, start_date)
VALUES ('admin_j', '5678', 'Joe', 'Admin', TO_DATE('2023-09-15', 'YYYY-MM-DD'));
INSERT INTO UserRole (username, role_id) VALUES ('admin_j', 2);

INSERT INTO Users (username, password, first_name, last_name, start_date, registration_date)
VALUES ('hybrid_j', '91011', 'Hybrid', 'User', TO_DATE('2023-08-01', 'YYYY-MM-DD'), TO_DATE('2023-08-01', 'YYYY-MM-DD'));

INSERT INTO UserRole (username, role_id) VALUES ('hybrid_j', 3);


INSERT INTO Users (username, password, first_name, last_name, registration_date)
VALUES ('raks', 'raks', 'Rakshith', 'Rav', TO_DATE('2023-05-01', 'YYYY-MM-DD'));
INSERT INTO UserRole (username, role_id) VALUES ('raks', 1);

INSERT INTO Users (username, password, first_name, last_name, registration_date)
VALUES ('girish', 'girish', 'Girish', 'Kan', TO_DATE('2023-05-12', 'YYYY-MM-DD'));
INSERT INTO UserRole (username, role_id) VALUES ('girish', 2);

  INSERT INTO Users (username, password, first_name, last_name, registration_date)
  VALUES ('jesith', 'jesith', 'Jesith', 'Yad', TO_DATE('2023-10-10', 'YYYY-MM-DD'));
  INSERT INTO UserRole (username, role_id) VALUES ('jesith', 3);


