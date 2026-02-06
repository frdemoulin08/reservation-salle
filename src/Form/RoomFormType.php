<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\RoomLayout;
use App\Entity\RoomType;
use App\Entity\Venue;
use App\Repository\RoomLayoutRepository;
use App\Repository\RoomTypeRepository;
use App\Repository\VenueRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoomFormType extends AbstractType
{
    public function __construct(
        private readonly VenueRepository $venueRepository,
        private readonly RoomTypeRepository $roomTypeRepository,
        private readonly RoomLayoutRepository $roomLayoutRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('venue', EntityType::class, [
                'label' => 'Site',
                'class' => Venue::class,
                'placeholder' => 'Sélectionner un site',
                'choice_label' => 'name',
                'query_builder' => function () {
                    return $this->venueRepository
                        ->createQueryBuilder('v')
                        ->orderBy('v.name', 'ASC');
                },
                'constraints' => [
                    new NotBlank(message: 'room.venue.required'),
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom de la salle',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'room.name.required'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('roomTypes', EntityType::class, [
                'label' => 'Types de salle',
                'class' => RoomType::class,
                'choice_label' => 'label',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'query_builder' => function () {
                    return $this->roomTypeRepository
                        ->createQueryBuilder('rt')
                        ->orderBy('rt.label', 'ASC');
                },
            ])
            ->add('roomLayouts', EntityType::class, [
                'label' => 'Configurations de salle',
                'class' => RoomLayout::class,
                'choice_label' => 'label',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'query_builder' => function () {
                    return $this->roomLayoutRepository
                        ->createQueryBuilder('rl')
                        ->orderBy('rl.label', 'ASC');
                },
            ])
            ->add('surfaceArea', NumberType::class, [
                'label' => 'Surface (m²)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => '0.01',
                    'min' => 0,
                ],
            ])
            ->add('seatedCapacity', IntegerType::class, [
                'label' => 'Capacité assise',
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('standingCapacity', IntegerType::class, [
                'label' => 'Capacité debout',
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('isPmrAccessible', CheckboxType::class, [
                'label' => 'Accès PMR',
                'required' => false,
            ])
            ->add('hasElevator', CheckboxType::class, [
                'label' => 'Ascenseur',
                'required' => false,
            ])
            ->add('hasPmrRestrooms', CheckboxType::class, [
                'label' => 'Sanitaires PMR',
                'required' => false,
            ])
            ->add('hasEmergencyExits', CheckboxType::class, [
                'label' => 'Issues de secours',
                'required' => false,
            ])
            ->add('isErpCompliant', CheckboxType::class, [
                'label' => 'Conformité ERP',
                'required' => false,
            ])
            ->add('erpType', TextType::class, [
                'label' => 'Type ERP',
                'required' => false,
            ])
            ->add('erpCategory', TextType::class, [
                'label' => 'Catégorie ERP',
                'required' => false,
            ])
            ->add('securityStaffRequired', CheckboxType::class, [
                'label' => 'Présence d’un agent/gardien',
                'required' => false,
            ])
            ->add('openingHoursSchema', TextareaType::class, [
                'label' => 'Horaires d’ouverture',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
            ])
            ->add('minRentalDurationMinutes', IntegerType::class, [
                'label' => 'Durée minimale (minutes)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('maxRentalDurationMinutes', IntegerType::class, [
                'label' => 'Durée maximale (minutes)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('bookingLeadTimeDays', IntegerType::class, [
                'label' => 'Délai de réservation (jours)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('cateringAllowed', CheckboxType::class, [
                'label' => 'Restauration autorisée',
                'required' => false,
            ])
            ->add('alcoholAllowed', CheckboxType::class, [
                'label' => 'Alcool autorisé',
                'required' => false,
            ])
            ->add('alcoholLegalNotice', TextareaType::class, [
                'label' => 'Mention légale alcool',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
            ])
            ->add('musicAllowed', CheckboxType::class, [
                'label' => 'Musique autorisée',
                'required' => false,
            ])
            ->add('sacemRequired', CheckboxType::class, [
                'label' => 'Déclaration SACEM requise',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
