import React, { useState, useEffect } from 'react';
import { eventsApi } from '../services/api';

interface ApiTestPageProps {}

const ApiTestPage: React.FC<ApiTestPageProps> = () => {
  const [apiData, setApiData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchApiData = async () => {
      try {
        setLoading(true);
        const response = await eventsApi.getBookNowEvents();
        setApiData(response);
        setError(null);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'An error occurred');
      } finally {
        setLoading(false);
      }
    };

    fetchApiData();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="text-xl">Loading API data...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          <strong>Error:</strong> {error}
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 p-6">
      <div className="max-w-6xl mx-auto">
        <h1 className="text-3xl font-bold mb-6 text-center">API Test Page - Book Now Events Data</h1>
        
        {/* API Summary */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 className="text-2xl font-semibold mb-4">API Response Summary</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="bg-blue-50 p-4 rounded">
              <h3 className="font-semibold">Success Status</h3>
              <p>{apiData?.success ? '✅ True' : '❌ False'}</p>
            </div>
            <div className="bg-green-50 p-4 rounded">
              <h3 className="font-semibold">Total Events</h3>
              <p>{apiData?.total || 0} events</p>
            </div>
            <div className="bg-purple-50 p-4 rounded">
              <h3 className="font-semibold">Available Categories</h3>
              <p>{apiData?.available_categories?.join(', ') || 'None'}</p>
            </div>
          </div>
        </div>

        {/* Raw JSON Data */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 className="text-2xl font-semibold mb-4">Full API Response (JSON)</h2>
          <pre className="bg-gray-100 p-4 rounded text-sm overflow-auto max-h-96">
            {JSON.stringify(apiData, null, 2)}
          </pre>
        </div>

        {/* Events Details */}
        {apiData?.data && apiData.data.length > 0 && (
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-2xl font-semibold mb-4">Events Breakdown</h2>
            <div className="space-y-6">
              {apiData.data.map((event: any, index: number) => (
                <div key={event.id || index} className="border border-gray-200 rounded-lg p-4">
                  <h3 className="text-xl font-semibold mb-3 text-blue-600">
                    Event #{event.id}: {event.title}
                  </h3>
                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Basic Info */}
                    <div>
                      <h4 className="font-semibold mb-2">Basic Information:</h4>
                      <ul className="text-sm space-y-1">
                        <li><strong>Title:</strong> {event.title}</li>
                        <li><strong>Description:</strong> {event.description}</li>
                        <li><strong>Location:</strong> {event.location}</li>
                        <li><strong>Category:</strong> {event.category}</li>
                        <li><strong>Slug:</strong> {event.slug}</li>
                      </ul>
                    </div>

                    {/* Date & Time */}
                    <div>
                      <h4 className="font-semibold mb-2">Date & Time:</h4>
                      <ul className="text-sm space-y-1">
                        <li><strong>Raw Date:</strong> {event.event_date}</li>
                        <li><strong>Formatted Date:</strong> {event.event_date_formatted}</li>
                        <li><strong>Formatted Time:</strong> {event.event_time_formatted}</li>
                        <li><strong>Booking Open:</strong> {event.is_booking_open ? '✅ Yes' : '❌ No'}</li>
                      </ul>
                    </div>

                    {/* Pricing */}
                    <div>
                      <h4 className="font-semibold mb-2">Pricing:</h4>
                      <ul className="text-sm space-y-1">
                        <li><strong>Price:</strong> {event.price}</li>
                        <li><strong>Price Display:</strong> {event.price_display}</li>
                        {event.ticket_pricing && (
                          <>
                            <li><strong>Base Price:</strong> RM{event.ticket_pricing.base_price}</li>
                            <li><strong>Adult Range:</strong> {
                              event.ticket_pricing.adult_price_range 
                                ? `RM${event.ticket_pricing.adult_price_range.min} - RM${event.ticket_pricing.adult_price_range.max}`
                                : 'N/A'
                            }</li>
                            <li><strong>Child Range:</strong> {
                              event.ticket_pricing.child_price_range 
                                ? `RM${event.ticket_pricing.child_price_range.min} - RM${event.ticket_pricing.child_price_range.max}`
                                : 'N/A'
                            }</li>
                          </>
                        )}
                      </ul>
                    </div>

                    {/* Image */}
                    <div>
                      <h4 className="font-semibold mb-2">Image:</h4>
                      <div className="text-sm">
                        <p><strong>Image URL:</strong></p>
                        <p className="break-all text-blue-600">{event.image_url}</p>
                        {event.image_url && (
                          <div className="mt-2">
                            <img 
                              src={event.image_url} 
                              alt={event.title}
                              className="w-32 h-20 object-cover rounded border"
                              onError={(e) => {
                                const img = e.target as HTMLImageElement;
                                img.src = 'https://via.placeholder.com/150x100/f0f0f0/666?text=Image+Not+Found';
                              }}
                            />
                          </div>
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Countries Available */}
                  {event.ticket_pricing?.countries_available && (
                    <div className="mt-4">
                      <h4 className="font-semibold mb-2">Countries Available:</h4>
                      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                        {event.ticket_pricing.countries_available.map((country: any, idx: number) => (
                          <div key={idx} className="bg-gray-50 p-2 rounded">
                            <strong>{country.name} ({country.code})</strong>
                            <br />
                            Adult: {country.currency_symbol}{country.adult_price}
                            <br />
                            Child: {country.currency_symbol}{country.child_price}
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* API Endpoint Info */}
        <div className="bg-white rounded-lg shadow-md p-6 mt-6">
          <h2 className="text-2xl font-semibold mb-4">API Configuration</h2>
          <div className="text-sm">
            <p><strong>API Base URL:</strong> https://admin.tfcmockup.com/api</p>
            <p><strong>Endpoint:</strong> /public/events/book-now</p>
            <p><strong>Full URL:</strong> https://admin.tfcmockup.com/api/public/events/book-now</p>
            <p><strong>Method:</strong> GET</p>
            <p><strong>Test Time:</strong> {new Date().toISOString()}</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ApiTestPage;