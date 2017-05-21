
import System.Environment
import System.Exit
import Data.Maybe
import Data.List


type Hformat = (Int,Int,[Int],Double)
type LevelData = ([(Int,Int)],[([Int],Double)])

-- main = do print "hello world"
main = main2
-- main = return test2 >>= putStrLn

-- TODO: stochastic gradient?

mainCalc :: (String,String) -> String
-- returns parameters for next sample, or "done" if optimization is done
-- first parameter is bounds file
-- mainCalc a = (show . parse_bounds . fst) a ++ "\n" ++ (show . parse_hist . snd) a
mainCalc (a,b) = mainCalc2 (parse_bounds a) (parse_hist b)


mainCalc2 :: Maybe [(Int,Int)] -> Maybe [Hformat] -> String
-- mainCalc2 (Just x) (Just y) = show x ++ "\n" ++ show y
-- mainCalc2 (Just x) (Just y) = mc3 x y
mainCalc2 (Just x) (Just y) = resultShow $ nextParams (x,y)
-- mainCalc2 (Just x) (Just y) = mainCalc3 x y
mainCalc2 _ _ = "Error: could not parse one of the files"
-------------------------------------------------------
-- start debug stuff

mc3 :: [(Int,Int)] -> [Hformat] -> String
-- mc3 x y = show t3
mc3 x y = show fl ++ show preres ++ show nns ++ "\n " ++ show mico ++ "\n" ++ show minpoint
  where t2 = translate_hist 2 (x,y)
        l2res = nextLParams [] t2
        nls = nextLineSearch t2
        fl = filterLine nls t2
        newco = (uncurry doLineSearch) fl
        xs = snd fl
        mico = findMinCoord $ snd t2
        minpoint = (fst.unzip) xs !! findMinIndex ((snd.unzip) xs)
        nns = neighbors minpoint $ (fst.unzip) xs
        preres = lineSearch2 (0,128) $ filter ((flip elem) nns . fst) xs
        f2 = filter ((flip elem) nns . fst ) xs
-- TODO: mico and minpoint inconsistent


-- end debug stuff
-------------------------------------------------------
-- start IO stuff
main2 :: IO ()
main2 = getArgs >>= parseargs >>= readTwoFiles >>= return . mainCalc >>= putStrLn

parseargs :: [String] -> IO String
parseargs [a]    = return a
parseargs _     = argc_err >> die_one

argc_err = putStrLn "Error: we need exactly one argument"
exit    = exitWith ExitSuccess
die_one     = exitWith (ExitFailure 1)

bipart :: Monad m => a -> a -> (a-> m b) -> (a-> m c) -> m (b,c)
bipart x1 x2 f1 f2 = f1 x1 >>= \ y -> f2 x2 >>= \ z -> return (y,z)

readTwoFiles :: String -> IO (String,String)
readTwoFiles = \ x -> bipart x x rf_bounds rf_hist

rf_bounds :: String -> IO String
rf_bounds a = readFile ("../"++ a ++ "/bounds.data")

rf_hist :: String -> IO String
rf_hist a = readFile (a++".data")

resultShow :: (Int,Int,[Int]) -> String
resultShow (_,_,[]) = "done"
resultShow (a,b,ys) = show a ++ " " ++ show b ++ " " ++ (intercalate "," $ map show ys)
-- END IO stuff

--------------------------------------------
-- start parsing stuff

maybefy :: (a -> Bool )-> a -> Maybe a
maybefy f x
  | f x = Just x
  | otherwise = Nothing

parse_two_files :: (String,String) -> Maybe ([(Int,Int)],[Hformat])
parse_two_files (a,b) = bipart a b parse_bounds parse_hist >>= c1 >>= c2
 where c1 = maybefy check_var_dim
       c2 = maybefy check_bounds

check_var_dim :: ([(Int,Int)],[Hformat]) -> Bool
check_var_dim (xs,[]) = True
check_var_dim (xs,y:_) = length xs == coeflistlen y

check_bounds :: ([(Int,Int)],[Hformat]) -> Bool
check_bounds (xs,[]) = True
check_bounds (xs,(_,_,y,_):ys) = checkBounds2 xs y && check_bounds (xs,ys)

checkBounds2 :: [(Int,Int)] -> [Int] -> Bool
checkBounds2 [] [] = True
checkBounds2 [a] [] = False
checkBounds2 [] [a] = False
checkBounds2 ((a,b):bs) (y:ys) = a<=y && y<=b

parse_bounds :: String -> Maybe [(Int,Int)]
parse_bounds a = mapM parse_bound_line (lines a)

parse_bound_line :: String -> Maybe (Int,Int)
parse_bound_line a = parse_bound_helper $ words a

parse_bound_helper :: [String] -> Maybe (Int,Int)
parse_bound_helper [a,b] = bipart a b maybe_read_int maybe_read_int
parse_bound_helper _ = Nothing

maybe_read_int :: String -> Maybe Int
maybe_read_int = listToMaybe . fst . unzip . i_reads

maybe_read_double :: String -> Maybe Double
maybe_read_double = listToMaybe . fst . unzip . f_reads

i_reads = reads :: String -> [(Int,String)]
f_reads = reads :: String -> [(Double,String)]

parse_hist :: String -> Maybe [Hformat]
parse_hist a = mapM parse_hist_line (lines a) >>= maybefy consistent_hist

consistent_hist :: [Hformat] -> Bool
consistent_hist hs = length xs == 0 || maximum xs == minimum xs
           where xs = map coeflistlen hs

coeflistlen :: Hformat -> Int
coeflistlen (_,_,a,_) = length a

parse_hist_line :: String -> Maybe Hformat
parse_hist_line a = parse_hist_helper1 $ words a

parse_hist_helper1 :: [String] -> Maybe Hformat
parse_hist_helper1 [a,b,c,d] = do x1 <- maybe_read_int a
                                  x2 <- maybe_read_int b
                                  x3s <- read_int_list c
                                  x4 <- maybe_read_double d
                                  return (x1,x2,x3s,x4)

parse_hist_helper1 _ = Nothing

read_int_list :: String -> Maybe [Int]
read_int_list a = mapM maybe_read_int (commasplit a)


commasplit :: String -> [String]
commasplit s = case dropWhile (== ',') s of
                 "" -> []
                 s' -> w : commasplit s''
                       where (w,s'') = break (== ',') s'
-- END parsing of files


-- start something

levels :: [(Int,Int)]
levels = [(120,120),(1000,800),(9000,0)]

levelTranslate :: Int -> Int -> Int -- returns 0,1,2,3
levelTranslate a b = 1+pos (a,b) levels

pos :: Eq a => a -> [a] -> Int
pos x [] = (-1)
pos y (x:xs)
  | x==y = 0
  | otherwise = if v>=0 then v+1 else -1 where v=pos y xs


translate_hist :: Int -> ([(Int,Int)],[Hformat]) -> LevelData
-- filterLevel2 _ _ = ([],[])
translate_hist l (bs,ys) = (bs,((map some34) .(filter ff))  ys)
   where some34 = (\ (_,_,x,y) -> (x,y))
         ff = (\(x,y,_,_) -> l==levelTranslate x y)

-- filterLevel

findMinIndex :: [Double] -> Int
findMinIndex [] = -1
findMinIndex ls = pos (minimum ls) ls

-- TODO: different behaviour for same f values
-- findMinCoord :: [([Int],Double)] -> [Int]
-- findMinCoord [] = [] -- ERROR
-- findMinCoord ls = fst $ ls !! (findMinIndex $ map snd ls)
findMinCoord = fMC2

fMC2 :: [([Int],Double)] -> [Int]
fMC2 [] = [] -- ERROR
fMC2 ls = fst $ minBy mysort ls
     where mysort (xs,fx) (ys,fy) = fx<fy || (fx==fy && sum xs < sum ys)
-- TODO

-- f x y is true if x<y
-- fails if list is empty
minBy :: ( a -> a -> Bool ) -> [a] -> a
minBy f x = foldl (\ x y -> if f x y then x else y) (head x ) x

isFeasible :: [(Int,Int)] -> [Int] -> Bool
isFeasible [] [] = True
isFeasible (b:bounds) (x:xs) = (&&) (fst b <= x && x <= snd b) $ isFeasible bounds xs
isFeasible _ _ = False -- ERROR

coordShift :: [Int] -> Int -> Int -> [Int]
coordShift xs _ 0 = xs
coordShift [] _ _ = []
coordShift (x:xs) 0 k = (x+k):xs
coordShift (x:xs) a k = x: coordShift xs (a-1) k

hasOptCriteria :: LevelData -> Bool
hasOptCriteria (bounds,samples) = and $
    map (hasOptCritCoord (bounds,samples)) [0..(length bounds -1)]

hasOptCritCoord :: LevelData -> Int -> Bool
hasOptCritCoord (bounds,samples) i =
    (hasCoord (bounds,samples) (coordShift mico i 1)) &&
    (hasCoord (bounds,samples) (coordShift mico i 2)) &&
    (hasCoord (bounds,samples) (coordShift mico i (-1))) &&
    (hasCoord (bounds,samples) (coordShift mico i (-2)))
  where mico = findMinCoord samples

hasSoftOptCrit :: LevelData -> Int -> Bool
hasSoftOptCrit (bounds,samples) i =
    ((hasCoord (bounds,samples) (coordShift mico i 1)) ||
    (hasCoord (bounds,samples) (coordShift mico i 2))) &&
    ((hasCoord (bounds,samples) (coordShift mico i (-1))) ||
    (hasCoord (bounds,samples) (coordShift mico i (-2))))
  where mico = findMinCoord samples

hasCoord :: LevelData -> [Int] -> Bool
-- returns true if not feasible
hasCoord (bounds,samples) xy = elem xy ((fst.unzip) samples) || not (isFeasible bounds xy)


startingPoint :: LevelData -> [Int]
startingPoint = map startingPoint2 . fst

startingPoint2 :: (Int,Int) -> Int
startingPoint2 (a,b)
  | a<0 = div (a+b) 2
  | a==0 = isqrt b
  | a>=1 = isqrt (a*b)
  | otherwise =  a

isqrt :: Int -> Int
isqrt = floor . sqrt . fromIntegral

nextLParams :: [Int] -> LevelData -> [Int]
-- nextLParams sp (bounds,[]) = startingPoint (bounds,[])
nextLParams sp (bounds,[]) = sp
nextLParams _ bs
  | hasOptCriteria bs = []
  | otherwise = replace nls mico newco
    where nls = nextLineSearch bs
          fl = filterLine nls bs
          newco = (uncurry doLineSearch) fl
          mico = findMinCoord $ snd bs

replace :: Int -> [Int] -> Int -> [Int]
-- replace
replace _ [] _ = []
replace n (x:xs) y
  | n==0 = y:xs
  | otherwise= x:replace (n-1) xs y

-- nextParams :: ([(Int,Int)],[Hformat]) -> (Int,Int,[Int])
-- nextParams x = if null l1res then
--   if null l2res then if null l3res then (0,0,[]) else r3 else r2 else r1
--     where l1res = nextLParams sp (translate_hist 1 x)
--           l2res = nextLParams sp (translate_hist 2 x)
--           l3res = nextLParams sp (translate_hist 3 x)
--           sp = startingPoint (fst x,[])
--           r1 = (fst $ levels !! 0,snd $ levels !! 0,l1res)
--           r2 = (fst $ levels !! 1,snd $ levels !! 1,l2res)
--           r3 = (fst $ levels !! 2,snd $ levels !! 2,l3res)

nextParams :: ([(Int,Int)],[Hformat]) -> (Int,Int,[Int])
nextParams x
  | not (null l1res) = (fst $ levels !!0,snd $ levels!!0,l1res)
  | not (null l2res) = (fst $ levels !!1,snd $ levels!!1,l2res)
  | not (null l3res) = (fst $ levels !!2,snd $ levels!!2,l3res)
  | null l3res = (0,0,[])
     where t1 = translate_hist 1 x
           t2 = translate_hist 2 x
           t3 = translate_hist 3 x
           l1res = nextLParams (startingPoint (fst x,[])) t1
           l2res = nextLParams (findMinCoord $ snd t1) t2
           l3res = nextLParams (findMinCoord $ snd t2) t3
 


onLine :: Int -> [Int] -> [Int] -> Bool
onLine i (x:xs) (y:ys) 
  | i<=0 = xs == ys
  | x==y = onLine (i-1) xs ys
  | otherwise = False
onLine _ _ _ = False

-- onLine :: [Int] -> [Int] -> Int
-- returns -1 if not on line, otherwise index of line
-- onLine (x:xs) (y:ys)
--   | x==y      = if v<0 then (-1) else v+1
--   | (y /= y && xs==ys) = 0
--   | otherwise = (-1)
--     where v = onLine xs ys
-- onLine _ _ = (-1)

countOnLine :: [Int] -> [[Int]] -> Int -> Int
countOnLine xs yss i = length $ filter (\ zs -> onLine i xs zs) yss

lineEvals :: [Int] -> LevelData -> [Int]
lineEvals xs (bs,ys) = map (\ x -> if hasOptCritCoord (bs,ys) x then -1 else countOnLine xs (fst(unzip ys)) x) [0..(length xs)]

multimaxIndex :: Ord a => [a] -> [Int]
-- returns indices where is maximal
-- multimaxIndex xs = filter ((==) (maximum xs)) xs
multimaxIndex xs = filter (\ i -> (xs !! i) == maximum xs) [0..(length xs -1)]

pickFrom :: [Int] -> Int -> Int
pickFrom xs seed = xs !! (mod seed (length xs))

nextLineSearch :: LevelData -> Int
-- returns coordinate for next line search
nextLineSearch (bounds,samples) = ((pickFrom . multimaxIndex) $
   lineEvals (findMinCoord samples) (bounds,samples)) (length samples)


convertLine :: Int -> ([Int],Double) -> (Int,Double)
convertLine i (xs,y) = (xs!!i,y)

filterLine :: Int -> LevelData -> ((Int,Int),[(Int,Double)])
filterLine i (bounds,samples) = (bounds !!i,(uniqfsg . mymap . myfil) samples)
  where mico = findMinCoord samples
        mymap = map (convertLine i)
        myfil = filter $ \ xs -> onLine i mico (fst xs)


uniqfsg :: [(Int,Double)] -> [(Int,Double)]
uniqfsg = nubBy myeq . sortBy mycomp
  where myeq (a,b) (c,d) = a==c
        mycomp (a,b) (c,d) = compare a c

-- start lineSearch
doLineSearch :: (Int,Int) -> [(Int,Double)] -> Int
-- input: bounds, point and value, returns new sample point
doLineSearch (l,h) xs
  | preres>=l = preres
  | preres==l-4096 && length nns /= 3 = preres
  | preres==l-4096 && elem (minpoint+2) ((fst.unzip) xs) = minpoint-2
  | preres==l-4096 && elem (minpoint-2) ((fst.unzip) xs) = minpoint+2
  | preres==l-4096 && even minpoint = minpoint+2
  | preres==l-4096 && odd minpoint = minpoint+2
--   | preres==l-4096 && elem l nns && minpoint==l+1 && elem (l+2) nns = l+3
--   | preres==l-4096 && elem h nns && minpoint==h-1 && elem (h-2) nns = h-3
  | otherwise = preres
    where minpoint = (fst.unzip) xs !! findMinIndex ((snd.unzip) xs)
          nns = neighbors minpoint $ (fst.unzip) xs
          preres = lineSearch2 (l,h) $ filter ((flip elem) nns . fst) xs

lineSearch2 :: (Int,Int) -> [(Int,Double)] -> Int
-- can assume l<=a<b<c<=h
lineSearch2 (l,h) [] = div (l+h) 2
lineSearch2 (l,h) [(a,fa)]
  | l==h = l-1025
  | a==l = a+1
  | a==h = a-1
  | even a = a+1
  | otherwise = a-1
lineSearch2 (l,h) [(a,fa),(b,fb)]
  | l==h = l-1025
  | b<=a = l-1025 -- err
  | l==a && b==a+1 = a+2
  | l==a = div (a+b) 2
  | h==b && b==a+1 = a-1
  | h==b = div (a+b) 2
  | fa<=fb = max l (a-2*(b-a))
  | fb<fa = min h (b+2*(b-a))
lineSearch2 (l,h) [(a,fa),(b,fb),(c,fc)]
  | minp /= min a (min b c)-1024 = minp
  | a>=b = l-1025 -- err
  | l==a && seqcon = c+1
  | h==c && seqcon = a-1
  | seqcon && even a = l - 4096
  | seqcon && odd a = l-4096
  | otherwise = l-1025
    where minp = minParabola (a,fa) (b,fb) (c,fc)
          seqcon = a+1==b && b+1==c
lineSearch2 (l,h) _ = l-1325
-- uses max three points as input

minParabola :: (Int,Double) -> (Int,Double) -> (Int,Double)  -> Int
-- guaranteed to be between x1 and x3, and !=x2
minParabola (x1,y1) (x2,y2) (x3,y3)
  | x1>=x2 = err1
  | x2>=x3 = err1
  | y1<y2 = err1
  | y2>y3 = err1
  | x1+1==x2 = if x3>x2+1 then div (x3+x2) 2 else err1
  | x3==x2+1 = div (x1+x2) 2
  | v2==0.0 = err1
  | minx < x1 = err1
  | minx > x3 = err1
  | minx == x1 = x1+1
  | minx == x3 = x3-1
  | minx == x2 = if even x1 then x2+1 else x2-1
  | otherwise = minx
    where err1=min x1 ( min x2 x3) -1024
          v1=y1*i (x3*x3-x2*x2)+y2*i (x1*x1-x3*x3)+y3*i (x2*x2-x1*x1)
          v2=y1*i (x2-x3)+y2*i (x3-x1)+y3*i (x1-x2)
          i = fromIntegral
          minx= round (-v1/(2*v2))


neighbors :: Int -> [Int] -> [Int]
-- assume that second argument is sorted, and first contained in second
neighbors a [] = [] -- error
neighbors a [b] = [b]
neighbors a [b,c]= [b,c]
neighbors a (x:y:xs)
  | a== x = [x,y]
  | a== y = [x] ++ neighbors a (y:xs)
  | otherwise = neighbors a (y:xs)

-- end lineSearch
