#! /bin/bash


gcc -O3 -lm predict.c fit.c -o predict
gcc -O3 game.c -o game

strip game
strip predict
