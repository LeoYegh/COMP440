-- ============================================================================
-- File: create_tables.sql
-- Description: 
--   SQL script to create the OnlineStore database and the 'user' table.
--   The 'user' table stores user credentials and basic profile information.
-- Usage:
--   1. Run this script in your MySQL environment.
--   2. It will create the database (if not exists) and the required table.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS OnlineStore;
USE OnlineStore;

CREATE TABLE user (
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    firstName VARCHAR(50),
    lastName VARCHAR(50),
    email VARCHAR(100) UNIQUE
);