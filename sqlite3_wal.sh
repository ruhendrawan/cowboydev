#!/bin/bash

sqlite3 database/database.sqlite 'PRAGMA journal_mode=WAL;'
