CREATE OR REPLACE DATABASE Dictionary;
USE Dictionary;

CREATE TABLE words(
    pk_wordId INT NOT NULL AUTO_INCREMENT,
    word VARCHAR(50),
    PRIMARY KEY(pk_wordId)
);

LOAD DATA LOCAL INFILE 'C:/xampp/htdocs/MyRepo2025-26/MyRepo2025-26/WEBAP/Differdange/Autosuggest AJAX/Dmytro/words.txt'
INTO TABLE words
LINES TERMINATED BY '\n'
(word);
