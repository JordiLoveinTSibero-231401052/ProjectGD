-- PostgreSQL database dump (tanpa data)
-- Versi PostgreSQL: 16.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';
SET default_table_access_method = heap;

-- Table: public.users
CREATE TABLE public.users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: public.favorites
CREATE TABLE public.favorites (
    id SERIAL PRIMARY KEY,
    user_name VARCHAR(100),
    team_id INTEGER NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    team_logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE
);

-- Table: public.matches
CREATE TABLE public.matches (
    home_team_name VARCHAR(100),
    away_team_name VARCHAR(100),
    home_team_id INTEGER,
    away_team_id INTEGER,
    home_score INTEGER,
    away_score INTEGER,
    match_status VARCHAR(50)
);

-- Table: public.profiles
CREATE TABLE public.profiles (
    user_id INTEGER PRIMARY KEY REFERENCES public.users(id) ON DELETE CASCADE,
    bio TEXT,
    birthday DATE,
    country VARCHAR(50),
    phone VARCHAR(20),
    twitter VARCHAR(255),
    facebook VARCHAR(255),
    google_plus VARCHAR(255),
    linkedin VARCHAR(255),
    instagram VARCHAR(255),
    profile_photo VARCHAR(255),
    name VARCHAR(100)
);

-- Ensure sequences are set to the correct values
SELECT pg_catalog.setval('public.favorites_id_seq', 1, false);
SELECT pg_catalog.setval('public.users_id_seq', 1, false);
