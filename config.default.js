// NOAA OPC Timeline Configuration
// Updated with productId fields for Web/TOC monitoring

const SHIFT_TIMELINES = {
    'Pac Regional': {
        shifts: {
            'Night Shift': [
                { name: 'Sea State', deadline: '2:55', done: false, link: 'https://ocean.weather.gov/shtml/P_00hrww.gif' },
                { name: 'RP1', deadline: '3:30', done: false, link: 'https://ocean.weather.gov/shtml/P_reg_00hrww.gif' },
                { name: 'Finalize Grids', deadline: '4:10', done: false, link: '' },
                { name: 'Offshores', deadline: '4:20', done: false, link: '', productId: 'OFFPZ5-00Z' },
                { name: 'Navtex', deadline: '6:30', done: false, link: '' },
                { name: 'RP1 ', deadline: '7:48', done: false, link: 'https://ocean.weather.gov/shtml/P_reg_00hrww.gif' },
                { name: '24 hour surface charts', deadline: '7:58', done: false, link: 'https://ocean.weather.gov/shtml/P_24hrsfc.gif' },
                { name: '24 hour wind wave charts', deadline: '8:08', done: false, link: 'https://ocean.weather.gov/shtml/P_24hrww.gif' },
                { name: 'Finalize Grids ', deadline: '10:10', done: false, link: '' },
                { name: 'Offshores ', deadline: '10:20', done: false, link: '', productId: 'OFFPZ5-06Z' },
                { name: 'Navtex ', deadline: '11:40', done: false, link: '' }
            ],
            'Day Shift': [
                { name: 'RP1', deadline: '14:55', done: false, link: 'https://ocean.weather.gov/shtml/P_reg_00hrww.gif' },
                { name: 'Finalize Grids', deadline: '16:10', done: false, link: '' },
                { name: 'Offshores', deadline: '16:20', done: false, link: '', productId: 'OFFPZ5-12Z' },
                { name: 'Navtex', deadline: '18:30', done: false, link: '' },
                { name: '24 hour surface charts', deadline: '18:22', done: false, link: 'https://ocean.weather.gov/shtml/P_24hrsfc.gif' },
                { name: '24 hour wind wave charts', deadline: '18:32', done: false, link: 'https://ocean.weather.gov/shtml/P_24hrww.gif' },
                { name: 'RP1 ', deadline: '19:13', done: false, link: 'https://ocean.weather.gov/shtml/P_reg_00hrww.gif' },
                { name: 'Finalize Grids ', deadline: '22:10', done: false, link: '' },
                { name: 'Offshores ', deadline: '22:20', done: false, link: '', productId: 'OFFPZ5-18Z' },
                { name: 'Navtex ', deadline: '23:40', done: false, link: '' }
            ]
        }
    },
    'Pac High Seas': {
        shifts: {
            'Night Shift': [
                { name: 'Prepare prelim analysis for WPC', deadline: '1:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB', deadline: '2:30', done: false, link: '' },
                { name: 'Open Unified and send final product', deadline: '3:30', done: false, link: 'https://ocean.weather.gov/shtml/P_full_00hrsfc.gif', productId: 'PYBA01-00Z' },
                { name: 'Prepare/Send High Seas Forecast', deadline: '4:20', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFEP1.php', productId: 'HSFEP1-00Z' },
                { name: 'Prepare 48 HR SFC', deadline: '8:13', done: false, link: 'https://ocean.weather.gov/shtml/P_48hrsfc.gif' },
                { name: 'Prepare 48 HR Wind Wave', deadline: '8:23', done: false, link: 'https://ocean.weather.gov/shtml/P_48hrww.gif' },
                { name: 'Prepare prelim analysis for WPC ', deadline: '7:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB ', deadline: '8:30', done: false, link: '' },
                { name: 'Open Unified and send final product ', deadline: '9:20', done: false, link: 'https://ocean.weather.gov/shtml/P_full_00hrsfc.gif', productId: 'PYBA03-06Z' },
                { name: 'Prepare/Send High Seas Forecast ', deadline: '10:20', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFEP1.php', productId: 'HSFEP1-06Z' }
            ],
            'Day Shift': [
                { name: 'Prepare prelim analysis for WPC', deadline: '13:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB', deadline: '14:30', done: false, link: '' },
                { name: 'Open Unified and send final product', deadline: '15:30', done: false, link: 'https://ocean.weather.gov/shtml/P_full_00hrsfc.gif', productId: 'PYBA05-12Z' },
                { name: 'Prepare/Send High Seas Forecast', deadline: '16:30', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFEP1.php', productId: 'HSFEP1-12Z' },
                { name: 'Prepare 48 HR SFC', deadline: '20:13', done: false, link: 'https://ocean.weather.gov/shtml/P_48hrsfc.gif' },
                { name: 'Prepare 48 HR Wind Wave', deadline: '20:23', done: false, link: 'https://ocean.weather.gov/shtml/P_48hrww.gif' },
                { name: 'Prepare prelim analysis for WPC ', deadline: '19:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB ', deadline: '20:30', done: false, link: '' },
                { name: 'Collaboration with Anchorage ', deadline: '20:30', done: false, link: '' },
                { name: 'Open Unified and send final product ', deadline: '21:20', done: false, link: 'https://ocean.weather.gov/shtml/P_full_00hrsfc.gif', productId: 'PYBA07-18Z' },
                { name: 'Prepare/Send High Seas Forecast ', deadline: '22:20', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFEP1.php', productId: 'HSFEP1-18Z' }
            ]
        }
    },
    'Atl Regional': {
        shifts: {
            'Night Shift': [
                { name: 'RA1', deadline: '3:15', done: false, link: 'https://ocean.weather.gov/shtml/A_reg_00hrww.gif' },
                { name: 'Offshores', deadline: '3:20', done: false, link: '', productId: 'OFFNT1-00Z' },
                { name: 'Finalize Grids', deadline: '4:10', done: false, link: '' },
                { name: 'Navtex', deadline: '4:40', done: false, link: '', productId: 'OFFN01-00Z' },
                { name: '24 hour surface charts', deadline: '8:05', done: false, link: 'https://ocean.weather.gov/shtml/A_24hrsfc.gif' },
                { name: '24 hour wind wave charts', deadline: '8:15', done: false, link: 'https://ocean.weather.gov/shtml/A_24hrww.gif' },
                { name: 'RA1 ', deadline: '8:30', done: false, link: 'https://ocean.weather.gov/shtml/A_reg_00hrww.gif' },
                { name: 'Finalize Grids ', deadline: '9:10', done: false, link: '' },
                { name: 'Offshores ', deadline: '9:20', done: false, link: '', productId: 'OFFNT1-06Z' },
                { name: 'Navtex ', deadline: '10:00', done: false, link: '', productId: 'OFFN01-06Z' },
                { name: 'Shift Log / Passdown', deadline: '9:00', done: false, link: '' }
            ],
            'Day Shift': [
                { name: 'RA1', deadline: '15:00', done: false, link: 'https://ocean.weather.gov/shtml/A_reg_00hrww.gif' },
                { name: 'Offshores', deadline: '15:20', done: false, link: '', productId: 'OFFNT1-12Z' },
                { name: 'Finalize Grids', deadline: '16:10', done: false, link: '' },
                { name: 'Navtex', deadline: '16:30', done: false, link: '', productId: 'OFFN01-12Z' },
                { name: 'Sea State', deadline: '18:00', done: false, link: 'https://ocean.weather.gov/shtml/A_00hrww.gif' },
                { name: '24 hour surface charts', deadline: '18:15', done: false, link: 'https://ocean.weather.gov/shtml/A_24hrsfc.gif' },
                { name: '24 hour wind wave charts', deadline: '18:30', done: false, link: 'https://ocean.weather.gov/shtml/A_24hrww.gif' },
                { name: 'RA1 ', deadline: '19:30', done: false, link: 'https://ocean.weather.gov/shtml/A_reg_00hrww.gif' },
                { name: 'Finalize Grids ', deadline: '21:10', done: false, link: '' },
                { name: 'Offshores ', deadline: '21:20', done: false, link: '', productId: 'OFFNT1-18Z' },
                { name: 'Navtex ', deadline: '21:50', done: false, link: '', productId: 'OFFN01-18Z' },
                { name: 'Shift Log / Passdown', deadline: '21:00', done: false, link: '' }
            ]
        }
    },
    'Atl High Seas': {
        shifts: {
            'Night Shift': [
                { name: 'Prepare prelim analysis for WPC', deadline: '1:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB', deadline: '2:30', done: false, link: '' },
                { name: 'Open Unified and send final product', deadline: '3:30', done: false, link: 'https://ocean.weather.gov/shtml/A_full_00hrsfc.gif', productId: 'PYAA01-00Z' },
                { name: 'Prepare/Send High Seas Forecast', deadline: '4:20', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFAT1.php', productId: 'HSFAT1-00Z' },
                { name: 'Prepare 48 HR SFC', deadline: '8:45', done: false, link: 'https://ocean.weather.gov/shtml/A_48hrsfc.gif' },
                { name: 'Prepare 48 HR Wind Wave', deadline: '8:55', done: false, link: 'https://ocean.weather.gov/shtml/A_48hrww.gif' },
                { name: 'Prepare prelim analysis for WPC ', deadline: '7:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB ', deadline: '8:30', done: false, link: '' },
                { name: 'Open Unified and send final product ', deadline: '9:20', done: false, link: 'https://ocean.weather.gov/shtml/A_full_00hrsfc.gif', productId: 'PYAA03-06Z' },
                { name: 'Prepare/Send High Seas Forecast ', deadline: '10:20', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFAT1.php', productId: 'HSFAT1-06Z' }
            ],
            'Day Shift': [
                { name: 'Prepare prelim analysis for WPC', deadline: '13:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB', deadline: '14:30', done: false, link: '' },
                { name: 'Open Unified and send final product', deadline: '15:30', done: false, link: 'https://ocean.weather.gov/shtml/A_full_00hrsfc.gif', productId: 'PYAA05-12Z' },
                { name: 'Prepare/Send High Seas Forecast', deadline: '16:20', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFAT1.php', productId: 'HSFAT1-12Z' },
                { name: 'Prepare 48 HR SFC', deadline: '20:13', done: false, link: 'https://ocean.weather.gov/shtml/A_48hrsfc.gif' },
                { name: 'Prepare 48 HR Wind Wave', deadline: '20:23', done: false, link: 'https://ocean.weather.gov/shtml/A_48hrww.gif' },
                { name: 'Prepare prelim analysis for WPC ', deadline: '19:00', done: false, link: '' },
                { name: 'Clip WPC and prepare/send to TAFB ', deadline: '20:30', done: false, link: '' },
                { name: 'Open Unified and send final product ', deadline: '21:20', done: false, link: 'https://ocean.weather.gov/shtml/A_full_00hrsfc.gif', productId: 'PYAA07-18Z' },
                { name: 'Prepare/Send High Seas Forecast ', deadline: '22:20', done: false, link: 'https://ocean.weather.gov/shtml/NFDHSFAT1.php', productId: 'HSFAT1-18Z' }
            ]
        }
    },
    'HFO Backup': {
        shifts: {
            'Night Shift': [
                { name: 'Offshore Forecast', deadline: '4:00', done: false, link: '' },
                { name: 'High Seas', deadline: '4:15', done: false, link: '' },
                { name: 'Navtex', deadline: '6:30', done: false, link: '' },
                { name: 'Offshore Forecast ', deadline: '10:00', done: false, link: '' },
                { name: 'High Seas ', deadline: '10:15', done: false, link: '' },
                { name: 'Navtex ', deadline: '12:30', done: false, link: '' }
            ],
            'Day Shift': [
                { name: 'Offshore Forecast', deadline: '16:00', done: false, link: '' },
                { name: 'High Seas', deadline: '16:15', done: false, link: '' },
                { name: 'Navtex', deadline: '18:30', done: false, link: '' },
                { name: 'Offshore Forecast ', deadline: '22:00', done: false, link: '' },
                { name: 'High Seas ', deadline: '22:15', done: false, link: '' },
                { name: 'Navtex ', deadline: '0:30', done: false, link: '' }
            ]
        }
    },
    'Outlook': {
        shifts: {
            'Night Shift': [],
            'Day Shift': [
                { name: '96hr Atlantic SFC Forecast VT12Z', deadline: '19:15', done: false, link: 'https://ocean.weather.gov/shtml/PWAM99.gif', productId: 'PWAM99-12Z' },
                { name: '96hr Atlantic Wind/Wave Forecast 12Z', deadline: '19:25', done: false, link: 'https://ocean.weather.gov/shtml/PJAM98.gif', productId: 'PJAM98-12Z' },
                { name: '96hr Pacific SFC Forecast VT12Z', deadline: '19:33', done: false, link: 'https://ocean.weather.gov/shtml/PWBM99.gif', productId: 'PWBM99-12Z' },
                { name: '96hr Pacific Wind/Wave Forecast 12Z', deadline: '19:43', done: false, link: 'https://ocean.weather.gov/shtml/PJBM98.gif', productId: 'PJBM98-12Z' },
                { name: '72hr AK SFC Forecast VT12Z (Non-Broadcast)', deadline: '20:43', done: false, link: 'https://ocean.weather.gov/shtml/PPCK98.gif', productId: 'PPCK98-12Z' },
                { name: '72hr Atlantic SFC Forecast VT12Z', deadline: '20:45', done: false, link: 'https://ocean.weather.gov/shtml/PPAK98.gif', productId: 'PPAK98-12Z' },
                { name: '72hr AK Wind/Wave Forecast 12Z (Non-Broadcast)', deadline: '20:53', done: false, link: 'https://ocean.weather.gov/shtml/PJCK88.gif', productId: 'PJCK88-12Z' },
                { name: '72hr Pacific SFC Forecast VT12Z', deadline: '20:53', done: false, link: 'https://ocean.weather.gov/shtml/PPBK98.gif', productId: 'PPBK98-12Z' },
                { name: '72hr Atlantic Wind/Wave Forecast 12Z', deadline: '20:55', done: false, link: 'https://ocean.weather.gov/shtml/PJAK88.gif', productId: 'PJAK88-12Z' },
                { name: '72hr Pacific Wind/Wave Forecast 12Z', deadline: '21:03', done: false, link: 'https://ocean.weather.gov/shtml/PJBK88.gif', productId: 'PJBK88-12Z' }
            ]
        }
    }
};
