```mermaid
flowchart TD
    subgraph Browser
        UI[User Interface (Blade + Tailwind)]
    end
    subgraph Backend
        UI -->|HTTP Request| Web[Laravel Router]
        Web --> Controllers[Controllers]
        Controllers --> Services[Service Layer]
        Services -->|DB Operations| DB[(PostgreSQL)]
        Services -->|SSL Check| SslChecker[SslCheckerService]
        Services -->|Queue Dispatch| Queue[Laravel Queue (DB)]
        Queue --> Jobs[Jobs: CheckDomainSslJob, SendWhatsappNotificationJob]
        Jobs -->|WhatsApp API| WA[WhatsApp Cloud API]
    end
    subgraph Scheduler
        Scheduler[Laravel Scheduler] -->|07:00 WIB| Jobs
    end
    subgraph Audit
        Controllers -->|Log Action| Audit[AuditLog Table]
    end
```