
create or replace database Credits;
use Credits;

CREATE TABLE Users (
    PersonId int not null primary key auto_increment,
    Name varchar(25) unique,
    Password varchar(100),
    Money int not null DEFAULT 10000
);

Create table LoanStatus (
    statusid int not null primary key auto_increment,
    statusText varchar(25)
);

Create table Loans (
    LoanId int not null primary key auto_increment,
    Creditor int,
    Debtor int not null,
    statusid int not null,
    FOREIGN KEY (statusid) REFERENCES LoanStatus(statusid),
    FOREIGN KEY (Creditor) REFERENCES Users(PersonId),
    FOREIGN KEY (Debtor) REFERENCES Users(PersonId)
);

Insert into LoanStatus(statusText) Values("Requested");
Insert into LoanStatus(statusText) Values("Credited");
Insert into LoanStatus(statusText) Values("Closed");

