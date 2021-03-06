
#define DATASIZE 24
#define SCOREFACTOR 1000


#define COEF_PREDICT_WIN 200
#define COEF_PREDICT_OTHER 100

#define POINTS_PLACE_1 15
#define POINTS_PLACE_2 9
#define POINTS_PLACE_3 6
#define POINTS_PLACE_4 4
#define POINTS_PLACE_5 3
#define POINTS_PLACE_6 2
#define POINTS_PLACE_7 1
#define POINTS_PLACE_8 0
#define POINTS_PLACE_9 0
#define POINTS_PLACE_10 0

#define FORGET_PROMILLE 40

const int pointsplacesum = ( POINTS_PLACE_1+ POINTS_PLACE_2+
POINTS_PLACE_3+ POINTS_PLACE_4+ POINTS_PLACE_5+ POINTS_PLACE_6+
POINTS_PLACE_7+ POINTS_PLACE_8+ POINTS_PLACE_9+ POINTS_PLACE_10);

#define COEF_PREDICT_1 ((int)((float)pointsplacesum*COEF_PREDICT_WIN*(1.0/FORGET_PROMILLE)))
#define COEF_PREDICT_2 ((int)((float)pointsplacesum*COEF_PREDICT_OTHER*(1.0/FORGET_PROMILLE)))

int placepoints[10]={ POINTS_PLACE_1, POINTS_PLACE_2,
  POINTS_PLACE_3, POINTS_PLACE_4, POINTS_PLACE_5, POINTS_PLACE_6,
  POINTS_PLACE_7, POINTS_PLACE_8, POINTS_PLACE_9, POINTS_PLACE_10};


