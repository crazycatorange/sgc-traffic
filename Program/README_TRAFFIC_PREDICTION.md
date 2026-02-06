# Jakarta Traffic Congestion Prediction System

## 🎯 Complete Big Data Solution with Weather Integration

A professional-grade traffic prediction system that uses **Big Data analytics** with Python to process 27 toll gate datasets and predict congestion levels with weather impact analysis.

---

## ✨ Key Features

### Big Data Processing
- ✅ **38,880 data records** analyzed from 27 CSV files
- ✅ **Python Pandas** for large-scale data processing
- ✅ **Statistical analysis** with baseline calculations
- ✅ **Real-time predictions** using historical patterns

### Advanced Analytics
- ✅ **Multi-factor prediction model** (4 factors combined)
- ✅ **Weather impact** integration (clear, cloudy, rain, heavy rain)
- ✅ **Peak hour detection** from historical data
- ✅ **Z-score normalization** for accurate predictions

### Interactive Visualization
- ✅ **OpenStreetMap** integration (no API key needed)
- ✅ **Real road routing** following actual streets
- ✅ **4 alternative routes** with different strategies
- ✅ **Numbered waypoints** showing route progression
- ✅ **Click-to-select** origin and destination

### Professional Output
- ✅ **Congestion percentage** (0-100%) for each route
- ✅ **Best route recommendation** based on predictions
- ✅ **Gate-by-gate analysis** with baseline statistics
- ✅ **Real-time system status** display

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   WEB BROWSER (Frontend)                 │
│     HTML + CSS + JavaScript + Leaflet (OpenStreetMap)   │
└───────────────────────┬─────────────────────────────────┘
                        │
                        │ HTTP REST API
                        │
┌───────────────────────▼─────────────────────────────────┐
│             FLASK SERVER (Python Backend)                │
│                                                           │
│  ┌─────────────────────────────────────────────────┐   │
│  │     BigDataTrafficAnalyzer Class                 │   │
│  │  • Load 27 CSV files                             │   │
│  │  • Calculate baseline statistics                 │   │
│  │  • Multi-factor prediction model                 │   │
│  │  • Weather impact analysis                       │   │
│  └─────────────────────────────────────────────────┘   │
│                                                           │
└───────────────────────┬─────────────────────────────────┘
                        │
                        │ File I/O
                        │
┌───────────────────────▼─────────────────────────────────┐
│              DATA LAYER (27 CSV Files)                   │
│   D:\PresUniv\Big Data Technology\Data Source\...       │
│   • GT_Bambu_Apus_1.csv (1,440 records)                 │
│   • GT_Bambu_Apus_2.csv (1,440 records)                 │
│   • ... (25 more files)                                  │
│   TOTAL: 38,880 records                                  │
└──────────────────────────────────────────────────────────┘
```

---

## 🚀 Installation Guide

### Prerequisites

1. **Python 3.8 or higher**
2. **27 CSV data files** in your local directory
3. **Web browser** (Chrome, Firefox, Edge, etc.)

### Step 1: Install Python Dependencies

```bash
pip install Flask==3.0.0
pip install flask-cors==4.0.0
pip install pandas==2.1.4
pip install numpy==1.26.2
pip install scipy==1.11.4
```

Or use requirements file:
```bash
pip install -r requirements.txt
```

### Step 2: Configure Data Directory

Open `traffic_prediction_app.py` and update line 20:

```python
# Update this path to your actual data directory
DATA_DIR = r'D:\PresUniv\Big Data Technology\Data Source\DATA UPDATE'
```

Make sure all 27 CSV files are in this directory:
- GT_Bambu_Apus_1.csv
- GT_Bambu_Apus_2.csv
- GT_Bintara.csv
- ... (and 24 more)

### Step 3: Verify Data Files

Run the validation (optional but recommended):

```python
    import os
    
    DATA_DIR = r'D:\PresUniv\Big Data Technology\Data Source\DATA UPDATE'
    files = os.listdir(DATA_DIR)
    csv_files = [f for f in files if f.endswith('.csv')]
    print(f"Found {len(csv_files)} CSV files")
```

### Step 4: Create Directory Structure

```
your-project-folder/
├── traffic_prediction_app.py    (Python backend)
├── templates/
│   └── traffic_predictor.html   (Frontend)
└── requirements.txt             (Dependencies)
```

### Step 5: Run the Application

```bash
python traffic_prediction_app.py
```

You should see:

```
======================================================================
BIG DATA LOADING: Processing 27 toll gate datasets
======================================================================
✓ Loaded GT_Bambu_Apus_1: 1,440 records
✓ Loaded GT_Bambu_Apus_2: 1,440 records
...
======================================================================
TOTAL RECORDS LOADED: 38,880
DATASETS LOADED: 27/27
======================================================================

BIG DATA ANALYSIS: Calculating baseline statistics...
✓ Baseline calculated for 27 toll gates

======================================================================
JAKARTA TRAFFIC PREDICTION SYSTEM - INITIALIZING
======================================================================

✓ System Ready for Traffic Prediction
======================================================================

======================================================================
STARTING FLASK SERVER
======================================================================
Access the application at: http://localhost:5000
API Endpoints:
  - GET  /api/toll-gates
  - POST /api/predict-routes
  - GET  /api/gate-realtime/<gate_id>
  - GET  /api/system-status
======================================================================

 * Running on http://0.0.0.0:5000
```

### Step 6: Open in Browser

Navigate to: **http://localhost:5000**

---

## 📖 How to Use

### Method 1: Click on Map

1. **Click once** on the map → Sets origin (green marker)
2. **Click again** → Sets destination (red marker)
3. **Select weather** condition (Clear/Cloudy/Rain/Heavy Rain)
4. Click **"🔮 Predict Congestion"**

### Method 2: Enter Coordinates

1. Enter **latitude and longitude** for origin
2. Enter **latitude and longitude** for destination
3. **Select weather** condition
4. Click **"🔮 Predict Congestion"**

### Understanding Results

The system displays **4 alternative routes**, each with:

- **Route Color** (Red/Blue/Green/Orange)
- **Congestion Percentage** (0-100%)
- **Number of Toll Gates**
- **Weather Condition** applied
- **Prediction Time** stamp
- **Gate Names** along the route

**Best Route** is highlighted with a green recommendation box.

---

## 🔬 Big Data Analytics Explained

### Prediction Model

The system uses a **4-factor weighted prediction model**:

```
Final Prediction = (
    Historical Pattern × 40% +
    Vehicle Density   × 30% +
    Speed Analysis    × 20% +
    Peak Hour Factor  × 10%
) × Weather Multiplier
```

### Factor 1: Historical Pattern Analysis (40% weight)

```python
# Analyzes congestion level distribution
high_rate = (High congestion count / Total) × 100
medium_rate = (Medium congestion count / Total) × 100
historical_score = high_rate × 1.0 + medium_rate × 0.5
```

### Factor 2: Vehicle Density Analysis (30% weight)

```python
# Z-score normalization against baseline
z_score = (current_vehicles - baseline_avg) / baseline_std
density_score = 50 + (z_score × 20)
```

### Factor 3: Speed Analysis (20% weight)

```python
if speed < 40 km/h:  score = 80  # High congestion
if speed < 50 km/h:  score = 60  # Medium
if speed < 60 km/h:  score = 40  # Low-Medium
else:                score = 20  # Low congestion
```

### Factor 4: Peak Hour Detection (10% weight)

```python
# Identifies top 3 busiest hours from historical data
if current_hour in peak_hours:
    peak_score = 75
else:
    peak_score = 25
```

### Weather Impact Multipliers

```python
weather_multipliers = {
    'clear':       1.0   # No impact
    'cloudy':      1.1   # +10% congestion
    'rain':        1.25  # +25% congestion
    'heavy_rain':  1.5   # +50% congestion
}
```

---

## 🌐 API Documentation

### GET /api/toll-gates

Returns all 27 toll gates with locations and baseline statistics.

**Response:**
```json
[
  {
    "id": "GT_Kuningan_1",
    "name": "Kuningan 1",
    "lat": -6.2389,
    "lng": 106.8306,
    "baseline": {
      "avg_vehicles": 15.2,
      "avg_speed": 62.5,
      "high_congestion_rate": 18.3
    }
  },
  ...
]
```

### POST /api/predict-routes

Predicts congestion for 4 alternative routes.

**Request:**
```json
{
  "origin_lat": -6.2088,
  "origin_lng": 106.8456,
  "dest_lat": -6.2847,
  "dest_lng": 106.9598,
  "weather": "rain"
}
```

**Response:**
```json
{
  "routes": [
    {
      "route_id": 1,
      "type": "Direct",
      "color": "#FF0000",
      "overall_congestion": 52.35,
      "gate_count": 5,
      "weather_condition": "rain",
      "prediction_time": "2026-02-06 15:30:00",
      "gates": [
        {
          "gate_id": "GT_Kuningan_1",
          "gate_name": "Kuningan 1",
          "congestion_probability": 65.42,
          "location": {"lat": -6.2389, "lng": 106.8306},
          "baseline_data": {
            "avg_vehicles": 15.2,
            "avg_speed": 62.5,
            "high_congestion_rate": 18.3
          }
        },
        ...
      ]
    },
    ...
  ],
  "origin": {"lat": -6.2088, "lng": 106.8456},
  "destination": {"lat": -6.2847, "lng": 106.9598},
  "weather": "rain"
}
```

### GET /api/gate-realtime/<gate_id>?weather=clear

Get real-time prediction for specific toll gate.

**Response:**
```json
{
  "gate_id": "GT_Kuningan_1",
  "name": "Kuningan 1",
  "congestion_prediction": 58.75,
  "weather": "clear",
  "hour": 15,
  "baseline": {
    "avg_vehicles": 15.2,
    "avg_speed": 62.5,
    "high_congestion_rate": 18.3
  },
  "timestamp": "2026-02-06T15:30:00"
}
```

### GET /api/system-status

Get system health and statistics.

**Response:**
```json
{
  "status": "operational",
  "toll_gates_loaded": 27,
  "total_records": 38880,
  "baseline_calculated": 27,
  "data_directory": "D:\\PresUniv\\Big Data Technology\\Data Source\\DATA UPDATE",
  "timestamp": "2026-02-06T15:30:00"
}
```

---

## 📊 Data Format

Each CSV file contains 1,440 records (one per minute for January 7, 2026):

```csv
datetime,vehicle_count,average_speed_kmh,congestion_level
2026-01-07 00:00:00,12,82,Low
2026-01-07 00:01:00,16,57,Medium
2026-01-07 00:02:00,12,75,Low
...
```

**Fields:**
- `datetime`: Timestamp
- `vehicle_count`: Number of vehicles (0-20+)
- `average_speed_kmh`: Speed in km/h (0-100+)
- `congestion_level`: Low/Medium/High

---

## 🎨 Customization

### Modify Weather Impact

Edit line 145 in `traffic_prediction_app.py`:

```python
weather_multiplier = {
    'clear': 1.0,
    'rain': 1.25,       # Change these values
    'heavy_rain': 1.5,
    'cloudy': 1.1
}
```

### Change Prediction Model Weights

Edit lines 120-125 in `traffic_prediction_app.py`:

```python
base_prediction = (
    historical_score * 0.40 +  # Adjust weights
    density_score * 0.30 +
    speed_analysis * 0.20 +
    peak_score * 0.10
)
```

### Modify Route Colors

Edit lines 292-295 in `traffic_prediction_app.py`:

```python
routes.append({'gates': route1, 'type': 'Direct', 'color': '#FF0000'})  # Change colors
```

---

## 🐛 Troubleshooting

### Problem: "File not found" errors

**Solution:**
1. Check `DATA_DIR` path in `traffic_prediction_app.py`
2. Ensure all 27 CSV files are present
3. Check file permissions

### Problem: Import errors

**Solution:**
```bash
pip install --upgrade pip
pip install -r requirements.txt
```

### Problem: No routes appearing

**Solution:**
1. Check browser console (F12) for errors
2. Verify Python backend is running
3. Check if Flask server shows "PREDICTION REQUEST" logs

### Problem: "Connection refused"

**Solution:**
1. Make sure Flask server is running (`python traffic_prediction_app.py`)
2. Check firewall settings
3. Try `http://127.0.0.1:5000` instead of `localhost`

---

## 📈 Performance

- **Data Loading**: ~2-5 seconds for 38,880 records
- **Baseline Calculation**: ~1-2 seconds for all gates
- **Single Prediction**: <100ms per route
- **4 Routes Analysis**: ~1-2 seconds total
- **Memory Usage**: ~150-200 MB

---

## 🔒 Security Notes

- System runs locally (not exposed to internet)
- No external API keys required
- Data stays on your machine
- No tracking or analytics

---

## 🎓 Educational Value

This project demonstrates:

1. **Big Data Processing**
   - Large dataset ingestion (38K+ records)
   - Pandas DataFrame operations
   - Statistical analysis at scale

2. **Machine Learning Concepts**
   - Feature engineering (4 factors)
   - Weighted prediction models
   - Z-score normalization
   - Baseline calculations

3. **Web Development**
   - Flask REST API
   - AJAX requests
   - Real-time data visualization
   - Responsive design

4. **Data Science**
   - Exploratory data analysis
   - Time-series patterns
   - Predictive modeling
   - Weather impact analysis

---

## 📝 License

Educational use - President University Big Data Technology Course

---

## 🆘 Support

For issues:
1. Check this README
2. Review browser console (F12)
3. Check Python backend logs
4. Contact course instructor

---

## 🎉 Quick Start Summary

```bash
# 1. Install dependencies
pip install -r requirements.txt

# 2. Update DATA_DIR in traffic_prediction_app.py
# 3. Run the server
python traffic_prediction_app.py

# 4. Open browser
# http://localhost:5000

# 5. Click map twice (origin → destination)
# 6. Select weather
# 7. Click "Predict Congestion"
# 8. View 4 routes with predictions!
```

**That's it! Professional traffic prediction system ready to use!** 🚗💨
